<?php

namespace Agnate\LazyQuest;

use \Agnate\LazyQuest\Dispatcher\SlackDispatcher;
use \Agnate\LazyQuest\Message\Channel;
use \Devristo\Phpws\Client\WebSocket;
use \Exception;
use \React\EventLoop\Factory;

class ServerConnection extends EntityBasic {

  public $server; // Can hold a Server or ServerResponder instance.
  public $commander;
  public $dispatcher;
  public $team;
  public $public_channels;
  public $im_channels;
  public $users;

  protected $websocket_url;
  protected $websocket_client;

  static $fields_int = array();
  static $fields_array = array('public_channels', 'im_channels', 'users');


  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);

    // Initialize dispatcher.
    if (!empty($this->dispatcher) && $this->dispatcher instanceof SlackDispatcher) {
      $this->dispatcher->connection = $this;
    }
  }

  /**
   * Get the IM channel for this Guild.
   * @param $guild Guild object that we want to find the IM channel ID for.
   */
  public function getGuildChannel(Guild $guild) {
    return $this->connection->im_channels[$guild->slack_id];
  }

  /**
   * Fetch all data to prepare for a websocket connection. If not using websocket (just want to send messages via commander), use this instead of connect().
   */
  public function init() {
    // Fetch all of the data we need before starting the RTM connection.
    $this->fetchChannels();
    $this->fetchUsers();
  }

  /**
   * Fetch all data and connect to the websocket for this team.
   */
  public function connect() {
    // Initialize the connection by fetching data we need.
    $this->init();

    // Create the connection.
    $this->createConnection();
    $this->startWebsocketServer();

    // Test the connection.
    $this->test();
  }

  /**
   * Initiate the websocket client and watch for responses.
   */
  protected function startWebsocketServer() {
    // Create the websocket client.
    $this->websocket_client = new WebSocket ($this->websocket_url, $this->server->websocket_loop, $this->server->logger);

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
      if (isset($data['type']) && $data['type'] == 'im_created') {
        $this->fetchChannels();
        return;
      }

      // If a new team member joins, refresh the list.
      if (isset($data['type']) && $data['type'] == 'team_join') {
        $this->fetchUsers();
        return;
      }

      // If a user changes their username, update their Guild.
      // if (isset($data['type']) && $data['type'] == 'user_change') {
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

    // Open the client.
    $this->websocket_client->open();
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

    // print "Got a message: " . $data['text'] . "\n";

    // Add additional information about the user.
    $data['user_info'] = $user;

    // Create a Session and see if it triggers an action and delivers a Message array.
    $session = new Session;
    $messages = $session->run($data);
    
    // print "Received " . count($messages) . " message(s).\n";

    // If this is not an array, we're done.
    if (!is_array($messages)) {
      $this->server->logger->err('Response from user input was not Message class. Response: ' . var_export($messages, true));

      // TODO: Send a friendly error message to user about the problem.
      $messages = array(
        Message::error('There was an error executing this command.', $session->data->channel),
      );
    }

    // Dispatch the messages.
    foreach ($messages as $message) {
      $this->dispatcher->dispatch($message);
    }
  }

  /**
   * Process a Slack button response.
   */
  public function update (Array $data) {
    /*$data contains:
    {
      "actions": [
        {
          "name": "recommend",
          "value": "yes"
        }
      ],
      "callback_id": "comic_1234_xyz",
      "team": {
        "id": "T47563693",
        "domain": "watermelonsugar"
      },
      "channel": {
        "id": "C065W1189",
        "name": "forgotten-works"
      },
      "user": {
        "id": "U045VRZFT",
        "name": "brautigan"
      },
      "action_ts": "1458170917.164398",
      "message_ts": "1458170866.000004",
      "attachment_id": "1",
      "token": "xAB3yVzGS4BQ3O9FACTa8Ho4",
      "original_message": "{\"text\":\"New comic book alert!\",\"attachments\":[{\"title\":\"The Further Adventures of Slackbot\",\"fields\":[{\"title\":\"Volume\",\"value\":\"1\",\"short\":true},{\"title\":\"Issue\",\"value\":\"3\",\"short\":true}],\"author_name\":\"Stanford S. Strickland\",\"author_icon\":\"https://api.slack.com/img/api/homepage_custom_integrations-2x.png\",\"image_url\":\"http://i.imgur.com/OJkaVOI.jpg?1\"},{\"title\":\"Synopsis\",\"text\":\"After @episod pushed exciting changes to a devious new branch back in Issue 1, Slackbot notifies @don about an unexpected deploy...\"},{\"fallback\":\"Would you recommend it to customers?\",\"title\":\"Would you recommend it to customers?\",\"callback_id\":\"comic_1234_xyz\",\"color\":\"#3AA3E3\",\"attachment_type\":\"default\",\"actions\":[{\"name\":\"recommend\",\"text\":\"Recommend\",\"type\":\"button\",\"value\":\"recommend\"},{\"name\":\"no\",\"text\":\"No\",\"type\":\"button\",\"value\":\"bad\"}]}]}",
      "response_url": "https://hooks.slack.com/actions/T47563693/6204672533/x7ZLaiVMoECAW50Gw1ZYAXEM"
    }
    

    See: https://api.slack.com/docs/message-buttons#overview

    Steps:
    - Validate that payload is real (use SLACK_OAUTH_VERIFICATION config to compare to "token")
    - Identify user/Guild who sent the message.
    - Retrieve the current command in queue (this likely needs to be stored in
        database - you can use the "callback_id" that's sent in the original message,
        which you can grab below).
    - Process the button they clicked (the "actions value").
    - Update the current command in queue with new results (if any).
    - Send back a payload update to refresh the buttons however necessary.
    */

    // Add additional information about the user.
    $data['user_info'] = $this->users[$data['user']['id']];

    // Start a new Session.
    $session = new Session;

    // Run our data through to see if anything should get updated.
    $messages = $session->update($data);

    // Dispatch any messages through the connection.
    foreach ($messages as $message) {
      $this->dispatcher->dispatch($message);
    }
  }

  /**
   * Test the connection.
   */
  protected function test() {
    $message = new Message (array(
      'channel' => new Channel (Channel::TYPE_PUBLIC),
      'text' => 'Lazy Quest server now online.',
    ));

    $success = $this->dispatcher->dispatch($message);;
  }

  /**
   * Create websocket connection.
   */
  protected function createConnection() {
    $response = $this->commander->execute('rtm.start', array());
    $body = $response->getBody();
    // Check for an okay response and get url.
    if (!empty($body['ok'])) $this->websocket_url = $body['url'];
    else throw new Exception ("Failed to initiate the rtm.start call. Response: " . var_export($body, true));
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
    else throw new Exception ("Failed to check channels.list for Team ID " . $this->team->tid . ". Response: " . var_export($body, true));

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
    else throw new Exception ("Failed to check groups.list for Team ID " . $this->team->tid . ". Response: " . var_export($body, true));

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
    else throw new Exception ("Failed to check im.list for Team ID " . $this->team->tid . ". Response: " . var_export($body, true));
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
    else throw new Exception ("Failed to check users.list for Team ID " . $this->team->tid . ". Response: " . var_export($body, true));
  }
}