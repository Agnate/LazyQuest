<?php

namespace Agnate\LazyQuest;

use \Agnate\LazyQuest\Dispatcher\SlackDispatcher;
// Classes for debugging.
use \Kint;
// Classes for logging.
use \Zend\Db\Adapter\Adapter;
use \Zend\Log\Logger;
use \Zend\Log\Writer\Db;
use \Zend\Log\Writer\Stream;
// Classes for websocket connections.
use \Frlnc\Slack\Core\Commander;
use \Frlnc\Slack\Http\CurlInteractor;
use \Frlnc\Slack\Http\SlackResponseFactory;
use \React\EventLoop\Factory;

class Server {

  public $interactor;
  public $teams;
  public $connections;
  public $websocket_loop;
  public $logger;

  protected static $logger_table = 'logs';
  protected static $logger_primary_key = 'log_id';

  /**
   * Initialize the server.
   */
  function __construct () {
    $this->interactor = new \Frlnc\Slack\Http\CurlInteractor;
    $this->interactor->setResponseFactory(new \Frlnc\Slack\Http\SlackResponseFactory);
    $this->commanders = array();
    $this->websocket_loop = \React\EventLoop\Factory::create();

    // Start up the logger.
    $this->startLogger();
  }

  /**
   * Start all the server connections to Slack.
   */
  public function start () {
    // Wipe out all ActionStates and Log entries in the database.
    $this->clearLogs();

    // Load all of the Team instances from the database.
    $this->teams = Team::loadMultiple(array());

    // Create a commander for each team.
    foreach ($this->teams as $team) {
      // Create the connection.
      $this->connect($team, TRUE);

      $this->logger->notice("Created a websocket connection for Team " . $team->team_id . " (tid: " . $team->tid . ").");
    }

    // Add any timers necessary.
    // $this->websocket_loop->addPeriodicTimer(2, 'timer_process_queue');
    // $this->websocket_loop->addPeriodicTimer(31, 'timer_reset_tavern');
    // $this->websocket_loop->addPeriodicTimer(32, 'timer_trickle_tavern');
    // $this->websocket_loop->addPeriodicTimer(33, 'timer_refresh_quests');
    // $this->websocket_loop->addPeriodicTimer(34, 'timer_leaderboard_standings');

    // Run the loop.
    $this->websocket_loop->run();
  }

  /**
   * Primarily used when creating a response Server to handle Slack buttons.
   * @param $team_id String team ID from Slack, used to create the ServerConnection.
   * @param $payload Response data sent from Slack.
   */
  public function handle ($team_id, Array $payload) {
    // Construct the ServerResponder so we can use the chat.update feature of bots.
    $team = Team::load(array('team_id' => $team_id));

    // Create the ServerConnection and link to the Team.
    // Note: We do not start up the Server, we just need it to initialize the connection.
    $connection = $this->connect($team, FALSE);

    // Handle the response through the connection.
    $connection->update($payload);
  }

  /**
   * Connect a team to the server.
   */
  public function connect (Team $team, $start_websocket = TRUE) {
    // Create the basics we need for the connection.
    $commander = new \Frlnc\Slack\Core\Commander($team->bot_access_token, $this->interactor);
    $dispatcher = new SlackDispatcher;
    
    // Create the connection.
    $connection = new ServerConnection (array(
      'server' => $this,
      'team' => $team,
      'commander' => $commander,
      'dispatcher' => $dispatcher,
    ));

    // Start websocket connection.
    if ($start_websocket) $connection->connect();
    else $connection->init();

    // No errors, so add it to the list of viable connections.
    $this->connections[$team->tid] = $connection;

    return $connection;
  }

  /**
   * Start up the logger if not already started.
   */
  protected function startLogger () {
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

  /**
   * Clear all log entries in the database.
   */
  protected function clearLogs () {
    // Empty the logger database table.
    $query = App::query("TRUNCATE TABLE " . static::$logger_table);
    $query->execute();

    // Empty the ActionState database table.
    $query = App::query("TRUNCATE TABLE " . ActionState::$db_table);
    $query->execute();
  }
}