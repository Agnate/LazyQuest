<?php

use \Agnate\RPG\EntityBasic;
use \Agnate\RPG\Dispatcher\SlackDispatcher;

use \Devristo\Phpws\Client\WebSocket;
use \React\EventLoop\Factory;

namespace Agnate\RPG;

class ServerConnection extends EntityBasic {

  public $server;
  public $commander;
  public $dispatcher;
  public $team;
  public $public_channels;
  public $im_channels;
  public $users;

  protected $websocket_url;
  protected $websocket_loop;
  protected $websocket_client;

  static $fields_int = array();
  static $fields_array = array('public_channels', 'im_channels', 'users');


  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);

    // Initialize dispatcher.
    if (!empty($this->dispatcher) && $this->dispatcher instanceof \Agnate\RPG\Dispatcher\SlackDispatcher) {
      $this->dispatcher->connection = $this;
    }
  }

  /**
   * Get the IM channel for this Guild.
   * @param $guild Guild object that we want to find the IM channel ID for.
   */
  public function getGuildChannel(\Agnate\RPG\Guild $guild) {
    return $this->connection->im_channels[$guild->slack_id];
  }

  /**
   * Fetch all data and connect to the websocket for this team.
   */
  public function connect() {
    // Fetch all of the data we need before starting the RTM connection.
    $this->fetchChannels();
    $this->fetchUsers();

    // Create the connection.
    $this->createConnection();
    $this->startWebsocketServer();

    // Test the connection.
    $this->test();
  }

  protected function startWebsocketServer() {
    // Create websocket connection.
    $this->websocket_loop = \React\EventLoop\Factory::create();

    // Add any timers necessary.
    // $this->websocket_loop->addPeriodicTimer(2, 'timer_process_queue');
    // $this->websocket_loop->addPeriodicTimer(31, 'timer_reset_tavern');
    // $this->websocket_loop->addPeriodicTimer(32, 'timer_trickle_tavern');
    // $this->websocket_loop->addPeriodicTimer(33, 'timer_refresh_quests');
    // $this->websocket_loop->addPeriodicTimer(34, 'timer_leaderboard_standings');

    // Create the websocket client.
    $this->websocket_client = new \Devristo\Phpws\Client\WebSocket ($this->websocket_url, $this->websocket_loop, $this->server->logger);

    $this->websocket_client->on("request", function($headers) {
      $this->server->logger->notice("Request object created.");
    });

    $this->websocket_client->on("handshake", function() {
      $this->server->logger->notice("Handshake received.");
    });

    $this->websocket_client->on("connect", function() {
      $this->server->logger->notice("Connected.");
    });

    $this->websocket_client->on("message", function($message) {
      // Only keep track of messages and reactions.
      $data = json_decode($message->getData(), true);

      // $this->server->logger->notice($data);

      // If a new IM channel is opened, refresh the list.
      // if (isset($data['type']) && $data['type'] == 'im_created') {
      //   global $im_channels, $commander;
      //   $im_channels = gather_im_channels($commander);
      //   return;
      // }

      // If a new team member joins, refresh the list.
      // else if (isset($data['type']) && $data['type'] == 'team_join') {
      //   global $im_channels, $commander;
      //   $user_list = gather_user_list($commander);
      //   return;
      // }

      // If a user changes their username, update their Guild.
      // else if (isset($data['type']) && $data['type'] == 'user_change') {
      //   update_user($data);
      //   return;
      // }

      // Message from the user.
      if (isset($data['type']) && $data['type'] == 'message' && !isset($data['subtype'])) {
        // Skip if we don't have the appropriate data.
        if (!isset($data['user'])) return;
        if (!isset($data['channel'])) return;

        // Send the input for processing.
        $this->onWebsocketMessage($data);
      }
    });

    $this->websocket_client->open();
    $this->websocket_loop->run();
  }

  /**
   * Process a received websocket message.
   */
  protected function onWebsocketMessage ($data) {
    $user_id = $data['user'];
    $channel = $data['channel'];

    // Get the personal message channel.
    if (!isset($this->im_channels[$user_id])) return;
    $im_channel = $this->im_channels[$user_id];

    // Check that it is a personal message channel.
    if ($channel != $im_channel) return;

    // Check that the user data exists.
    if (!isset($this->users[$user_id])) return;
    $user = $this->users[$user_id];

    $this->server->logger->notice("Got personal message from user: " . var_export($data, TRUE));

    // Nothing to do if there's no text.
    if (empty($data['text'])) return;

    // Get the message text.
    $text = $data['text'];

    // Bust it up and send it as a command to RPGSession.
    /*
    'type' => 'message',
    'channel' => 'D286C33AR',
    'user' => 'U0265JBJW',
    'text' => 'hello',
    'ts' => '1473045021.000013',
    'team' => 'T025KTDB7',
    */
    $session_data = $this->populateUserData($user_id, $data['team'], $text);
    $session = new \Agnate\RPG\Session ();
    $messages = $session->run($text, $session_data);
    //$this->server->logger->notice($response);

    // Response must be in the form of an Array of Message instances.
    if (!is_array($messages) && $messages instanceof \Agnate\RPG\Message)
      $messages = array($messages);

    // If this is not an array, we're done.
    if (!is_array($messages)) {
      $this->server->logger->err('Response from user input was not Message class. Response: ' . $messages);

      // TODO: Send a friendly error message to user about the problem.
      return;
    }

    // Dispatch the messages.
    foreach ($messages as $message) {
      $this->dispatcher->dispatch($message);
    }
  }

  /**
   * Convert a Slack data response into usable game classes.
   */
  protected function populateUserData($slack_user_id, $slack_team_id, $text, $debug = FALSE) {
    return array(
      'slack_user_id' => $slack_user_id,
      'slack_team_id' => $slack_team_id,
      'input' => $text,
    );
  }

  /**
   * Test the connection.
   */
  protected function test() {
    $message = new \Agnate\RPG\Message (array(
      'channel' => new \Agnate\RPG\Message\Channel (\Agnate\RPG\Message\Channel::TYPE_PUBLIC),
      'text' => 'Lazy Quest server now online.',
    ));

    $this->dispatcher->dispatch($message);
  }

  /**
   * Create websocket connection.
   */
  protected function createConnection() {
    $response = $this->commander->execute('rtm.start', array());
    $body = $response->getBody();
    // Check for an okay response and get url.
    if (!empty($body['ok'])) $this->websocket_url = $body['url'];
    else throw new \Exception ("Failed to initiate the rtm.start call. Response: " . var_export($body, true));
  }

  /**
   * Fetch list of Slack channels (public and group) this team is in.
   */
  protected function fetchChannels() {
    // Get list of public channels this bot is in.
    $response = $this->commander->execute('channels.list');
    $body = $response->getBody();
    // Check for an okay response.
    if (!empty($body['ok'])) {
      foreach ($body['channels'] as $channel) {
        if (!$channel['is_member'] || !$channel['is_channel'] || $channel['is_archived']) continue;
        $this->public_channels[$channel['id']] = $channel['name'];
      }
    }
    else throw new \Exception ("Failed to check channels.list for Team ID " . $this->team->tid . ". Response: " . var_export($body, true));

    // Also check private groups, in case people didn't want to use public channels.
    $response = $this->commander->execute('groups.list');
    $body = $response->getBody();
    // Check for an okay response.
    if (!empty($body['ok'])) {
      foreach ($body['groups'] as $group) {
        if ($group['is_archived']) continue;
        $this->public_channels[$group['id']] = $group['name'];
      }
    }
    else throw new \Exception ("Failed to check groups.list for Team ID " . $this->team->tid . ". Response: " . var_export($body, true));

    // Get list of IM channels.
    $response = $this->commander->execute('im.list');
    $body = $response->getBody();
    // Check for an okay response.
    if (!empty($body['ok'])) {
      foreach ($body['ims'] as $im) {
        if ($im['is_user_deleted']) continue;
        $this->im_channels[$im['user']] = $im['id'];
      }
    }
    else throw new \Exception ("Failed to check im.list for Team ID " . $this->team->tid . ". Response: " . var_export($body, true));
  }

  /**
   * Fetch all of the users in the Slack team.
   */
  protected function fetchUsers () {
    $response = $this->commander->execute('users.list', array());
    $body = $response->getBody();

    if (!empty($body['ok'])) {
      foreach ($body['members'] as $member) {
        if ($member['deleted']) continue;
        $this->users[$member['id']] = $member;
      }
    }
    else throw new \Exception ("Failed to check users.list for Team ID " . $this->team->tid . ". Response: " . var_export($body, true));
  }
}