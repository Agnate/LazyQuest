<?php

use \Kint;

use \Agnate\RPG\ServerConnection;
use \Agnate\RPG\Session;
use \Agnate\RPG\Dispatcher\SlackDispatcher;

// Classes for logging.
use \Zend\Db\Adapter\Adapter;
use \Zend\Log\Logger;
use \Zend\Log\Writer\Db;
use \Zend\Log\Writer\Stream;

// Classes for websocket connections.
use \Frlnc\Slack\Core\Commander;
use \Frlnc\Slack\Http\CurlInteractor;
use \Frlnc\Slack\Http\SlackResponseFactory;

namespace Agnate\RPG;

class Server {

  public $interactor;
  public $teams;
  public $connections;
  public $logger;

  protected static $logger_table = 'logs';
  protected static $logger_primary_key = 'log_id';

  /**
   * Initialize the server.
   */
  function __construct() {
    $this->interactor = new \Frlnc\Slack\Http\CurlInteractor;
    $this->interactor->setResponseFactory(new \Frlnc\Slack\Http\SlackResponseFactory);
    $this->commanders = array();

    // Start up the logger.
    $this->startLogger();
  }

  /**
   * Start all the server connections to Slack.
   */
  public function start() {
    $this->teams = Team::loadMultiple(array());

    // Create a commander for each team.
    foreach ($this->teams as $team) {
      // Create the basics we need for the connection.
      $commander = new \Frlnc\Slack\Core\Commander($team->bot_access_token, $this->interactor);
      $dispatcher = new \Agnate\RPG\Dispatcher\SlackDispatcher;
      
      // Create the connection.
      $connection = new ServerConnection (array(
        'server' => $this,
        'team' => $team,
        'commander' => $commander,
        'dispatcher' => $dispatcher,
      ));

      // Start websocket connection.
      $connection->connect();

      // No errors, so add it to the list of viable connections.
      $connections[$team->tid] = $connection;
    }
  }

  /**
   * Start up the logger if not already started.
   */
  protected function startLogger() {
    if (!empty($this->logger)) return;

    // Create logger.
    $this->logger = new \Zend\Log\Logger;

    // Create text log writer.
    $txt_writer = new \Zend\Log\Writer\Stream (GAME_SERVER_LOG_FILE . '/app_' . date('Y-m-d-H-i-s') . '.log');
    $this->logger->addWriter($txt_writer);

    // Create the logger databalse table if it doesn't exist.
    if (!App::database()->tableExists(static::$logger_table)) {
      $fields = array();
      $fields[] = "log_id INT(11) UNSIGNED AUTO_INCREMENT";
      $fields[] = "type TINYINT(1) NOT NULL";
      $fields[] = "type_name VARCHAR(30) NOT NULL";
      $fields[] = "message LONGTEXT NOT NULL";
      $fields[] = "created INT(10) UNSIGNED NOT NULL";

      // Make the database.
      App::database()->createTable(static::$logger_table, static::$logger_primary_key, $fields);
    }

    // Create database log writer.
    $db_config = array(
      'driver' => 'Pdo_Mysql',
      'database' => DB_NAME,
      'host' => DB_HOST,
      'username' => DB_USER,
      'password' => DB_PASS,
    );
    $db_mapping = array(
      'priority'  => 'type',
      'priorityName' => 'type_name',
      'message'   => 'message',
      'timestamp' => 'created',
    );
    $db = new \Zend\Db\Adapter\Adapter ($db_config);
    $db_writer = new \Zend\Log\Writer\Db ($db, static::$logger_table, $db_mapping);
    $this->logger->addWriter($db_writer);

    // Intercept all exceptions.
    \Zend\Log\Logger::registerErrorHandler($this->logger);
  }
}