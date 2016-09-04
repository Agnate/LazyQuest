<?php

use \Agnate\RPG\EntityBasic;
use \Agnate\RPG\Dispatcher\SlackDispatcher;

namespace Agnate\RPG;

class ServerConnection extends EntityBasic {

  public $commander;
  public $dispatcher;
  public $logger;
  public $team;
  public $public_channels;
  public $im_channels;

  static $fields_int = array();
  static $fields_array = array('public_channels', 'im_channels');


  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);

    // Initialize dispatcher.
    if (!empty($this->dispatcher) && $this->dispatcher instanceof \Agnate\RPG\Dispatcher\SlackDispatcher) {
      $this->dispatcher->connection = $this;
    }
  }

  /**
   * Test the connection.
   */
  public function test() {
    $message = new \Agnate\RPG\Message (array(
      'channel' => new \Agnate\RPG\Message\Channel (\Agnate\RPG\Message\Channel::TYPE_PUBLIC),
      'text' => '[TEST] Hello, world!',
    ));

    $this->dispatcher->dispatch($message);
  }

  public function getGuildChannel(\Agnate\RPG\Guild $guild) {
    return $this->connection->im_channels[$guild->slack_id];
  }

  /**
   * Fetch list of Slack channels (public and group) this team is in.
   */
  public function fetchChannels() {
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
  }
}