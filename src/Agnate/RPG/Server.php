<?php

use \Agnate\RPG\ServerConnection;
use \Agnate\RPG\Session;
use \Agnate\RPG\Dispatcher\SlackDispatcher;

// Create API call to start websocket connection.
use \Frlnc\Slack\Http\SlackResponseFactory;
use \Frlnc\Slack\Http\CurlInteractor;
use \Frlnc\Slack\Core\Commander;

namespace Agnate\RPG;

class Server {

  public $interactor;
  public $teams;
  public $connections;

  /**
   * Initialize the server.
   */
  function __construct() {
    $this->interactor = new \Frlnc\Slack\Http\CurlInteractor;
    $this->interactor->setResponseFactory(new \Frlnc\Slack\Http\SlackResponseFactory);
    $this->commanders = array();
  }

  public function start() {
    $this->teams = Team::loadMultiple(array());

    // Create a commander for each team.
    foreach ($this->teams as $team) {
      // Create the basics we need for the connection.
      $commander = new \Frlnc\Slack\Core\Commander($team->bot_access_token, $this->interactor);
      $dispatcher = new \Agnate\RPG\Dispatcher\SlackDispatcher;
      
      // Create the connection.
      $connection = new ServerConnection (array(
        'team' => $team,
        'commander' => $commander,
        'dispatcher' => $dispatcher,
      ));

      // Fetch the channels this team's bot is in.
      $connection->fetchChannels();

      // Test the connection.
      $connection->test();

      // For testing, send message right away.
      // foreach ($connection->channels as $channel_id => $channel_name) {
      //   $response = $connection->commander->execute('chat.postMessage', array(
      //     'channel' => $channel_id,
      //     'as_user' => TRUE,
      //     'text' => 'Hello, world!',
      //   ));
      //   d($response);
      // }

      // No errors, so add it to the list of viable connections.
      $connections[$team->tid] = $connection;
    }
  }

}