<?php

namespace Agnate\LazyQuest;

use \Agnate\LazyQuest\Data\FormatData;
use \Agnate\LazyQuest\Data\TokenData;

class App {

  protected static $started = FALSE;
  protected static $database;
  protected static $logger;
  protected static $cache;
  protected static $tokens;
  protected static $formats;

  protected static $logger_table = 'logs';
  protected static $logger_primary_key = 'log_id';
  

  /* Log priorities: */
  const EMERG   = 0;  // Emergency: system is unusable
  const ALERT   = 1;  // Alert: action must be taken immediately
  const CRIT    = 2;  // Critical: critical conditions
  const ERR     = 3;  // Error: error conditions
  const WARN    = 4;  // Warning: warning conditions
  const NOTICE  = 5;  // Notice: normal but significant condition
  const INFO    = 6;  // Informational: informational messages
  const DEBUG   = 7;  // Debug: debug messages

  private function __construct() {}


  /* =================================
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  ==================================== */

  /**
   * Start the App and initialize the Database.
   */
  public static function start () {
    if (static::$started) return static::$started;

    // Create the database connection if it's not there.
    if (empty(static::$database)) {
      static::$database = new Database (DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }

    // Initialize the cache.
    static::cache();

    // Load up all the TokenData from JSON files into Cache.
    static::loadTokens();

    // Load up all the FormatData from JSON files into Cache.
    static::loadFormats();

    // Set App to started.
    static::$started = TRUE;

    return static::$started;
  }

  /**
   * Get the existing tokens loaded by App.
   */
  public static function tokens () {
    if (!isset(static::$tokens)) static::loadTokens();

    return static::$tokens;
  }

  /**
   * List of token names to filter the token list by.
   * @param Array $token_names List of token names (as strings) to get the TokenData for.
   * @return Array List of token name => TokenData pairs. If an item in the $token_names list could not be found,
   *    it will not be included in the returned list.
   */
  public static function getTokens ($token_names) {
    return array_intersect_key(static::tokens(), array_flip($token_names));
  }

  /**
   * Load up all the TokenData from JSON files into Cache.
   */
  protected static function loadTokens () {
    // Glob all JSON files and store randomization tokens into the Cache for each file.
    foreach (glob(GAME_SERVER_ROOT . "/data/tokens/[!__]*.json") as $filename) {
      // Scrub the filename.
      $key = substr($filename, strrpos($filename, '/') + 1, -5);

      // Check if there is already an entry in Cache. No need to add it again if it's there.
      if (TokenData::isCached($key)) $token = new TokenData ($key);
      // Create the new TokenData (including the original) and store in Cache.
      else $token = TokenData::fromJsonFile($key, $filename);

      static::$tokens[$key] = $token;
    }
  }

  /**
   * Get the existing formats loaded by App.
   */
  public static function formats () {
    if (!isset(static::$formats)) static::loadFormats();

    return static::$formats;
  }

  /**
   * Get the FormatData instance for the format name provided.
   * @param string $format_name The name of the format to get the FormatData for.
   * @return FormatData Returns the FormatData instance found, FALSE otherwise.
   */
  public static function getFormat ($format_name) {
    if (!isset(static::$formats[$format_name])) return FALSE;
    return static::$formats[$format_name];
  }

  /**
   * Load up all the FormatData from JSON files into Cache.
   */
  protected static function loadFormats () {
    // Glob all JSON files and store randomization formats into the Cache for each file.
    foreach (glob(GAME_SERVER_ROOT . "/data/formats/[!__]*.json") as $filename) {
      // Scrub the filename.
      $key = substr($filename, strrpos($filename, '/') + 1, -5);

      // Check if there is already an entry in Cache. No need to add it again if it's there.
      if (FormatData::isCached($key)) $format = new FormatData ($key);
      // Create the new FormatData (including the original) and store in Cache.
      else $format = FormatData::fromJsonFile($key, $filename);

      static::$formats[$key] = $format;
    }
  }

  /**
   * Get the Database used in this Session.
   * @return Database Returns the Database generated for this App.
   */
  public static function database () {
    if (!static::$started) static::start();

    return static::$database;
  }

  /**
   * Prepare a string query to use in Database (PDO).
   * @param $query String query to pass to Database's prepare() function.
   */
  public static function query ($query) {
    if (!static::$started) static::start();

    return static::$database->prepare($query);
  }

  /**
   * Get the logger instance so we can report stuff.
   * @return \Zend\Log\Logger Instance of Logger from Zend framework.
   */
  public static function logger () {
    if (!empty(static::$logger)) return static::$logger;

    // Create logger.
    static::$logger = new \Zend\Log\Logger;

    // Create text log writer.
    $txt_writer = new \Zend\Log\Writer\Stream (GAME_SERVER_LOG_FILE . '/app_' . date('Y-m-d-H-i-s') . '.log');
    static::$logger->addWriter($txt_writer);

    // Create the logger databalse table if it doesn't exist.
    if (!static::database()->tableExists(static::$logger_table)) {
      $fields = array();
      $fields[] = "log_id INT(11) UNSIGNED AUTO_INCREMENT";
      $fields[] = "type TINYINT(1) NOT NULL";
      $fields[] = "type_name VARCHAR(30) NOT NULL";
      $fields[] = "message LONGTEXT NOT NULL";
      $fields[] = "created INT(10) UNSIGNED NOT NULL";

      // Make the database.
      static::database()->createTable(static::$logger_table, static::$logger_primary_key, $fields);
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
    static::$logger->addWriter($db_writer);

    // Intercept all exceptions.
    \Zend\Log\Logger::registerErrorHandler(static::$logger);

    return static::$logger;
  }

  /**
   * Get the cachine object used to cache unimportant data.
   */
  public static function cache () {
    if (!empty(static::$cache)) return static::$cache;

    // Create a new Cache instance.
    static::$cache = new Cache (GAME_CACHE_SERVER, GAME_CACHE_PORT);

    return static::$cache;
  }
  
  /**
   * Convert Markup (used in Slack) to HTML for browser debug viewing.
   */
  public static function convertMarkup ($string) {
    $info = array(
      '/:([A-Za-z0-9_\-\+]+?):/' => '<img class="icon" src="/debug/icons/\1.png" width="22px" height="22px">',
      '/\\n/' => '<br>',
      '/\*(.*?)\*/' => '<strong>\1</strong>',
      '/\b_((?:__|[\s\S])+?)_\b|^\*((?:\*\*|[\s\S])+?)\*(?!\*)/' => '<em>\1</em>',
      '/(`+)\s*([\s\S]*?[^`])\s*\1(?!`)/' => '<code>\2</code>',
    );

    return preg_replace(array_keys($info), array_values($info), $string);
  }
}