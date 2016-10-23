<?php

namespace Agnate\LazyQuest;

use \Agnate\LazyQuest\Team;
use \Agnate\LazyQuest\Data\CacheData;
use \Agnate\LazyQuest\Data\FormatData;
use \Agnate\LazyQuest\Data\TokenData;
use \Agnate\LazyQuest\Data\WordsData;

class App {

  protected static $started = FALSE;
  protected static $database;
  protected static $logger;
  protected static $cache;
  protected static $words;
  protected static $tokens;
  protected static $formats;
  protected static $teams;

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

    // Set App to started.
    static::$started = TRUE;

    // Load all other data.
    static::load();

    return static::$started;
  }

  /**
   * Load up any other data the App needs.
   */
  protected static function load () {
    // Load all Slack teams.
    static::loadTeams();

    // Load up all the WordsData from JSON files into Cache.
    static::loadWords();

    // Load up all the TokenData from JSON files into Cache.
    static::loadTokens();

    // Load up all the FormatData from JSON files into Cache.
    static::loadFormats();
  }

  /**
   * Load all the teams.
   */
  protected static function loadTeams () {
    static::$teams = Team::loadMultiple([]);
  }

  /**
   * Load cache data from JSON files.
   * @param string $directory The path of the directory inside the /data folder. Example: for /data/words, use: "words".
   * @param Array $destination The list to store the cache items into.
   * @param string $class The class name to load the data into. MUST implement CacheData class. Default is \Agnate\LazyQuest\Data\CacheData.
   */
  protected static function loadCacheData ($directory, &$destination, $class = '\Agnate\LazyQuest\Data\CacheData') {
    // Load teams if they have not been loaded.
    static::loadTeams();

    // Glob all JSON files and store words into the Cache for each file.
    foreach (glob(GAME_SERVER_ROOT . "/data/" . $directory . "/[!__]*.json") as $filename) {
      // Scrub the filename.
      $key = substr($filename, strrpos($filename, '/') + 1, -5);

      // If the original exists, load it.
      if ($class::isCached(NULL, $key)) $original = new $class (NULL, $key);
      else $original = $class::fromJsonFile(NULL, $key, $filename);

      // Save original to list.
      $destination[$original->key()] = $original;

      // Create a cache instance for each team.
      foreach (static::$teams as $team) {
        $team_id = $team->team_id;

        // Check if there is already an entry in Cache. No need to add it again if it's there.
        if ($class::isCached($team_id, $key)) $cache = new $class ($team_id, $key);
        // Create the new CacheData (including the original) and store in Cache.
        else $cache = $class::fromOriginal($original, $team_id);

        // Add to cache list.
        $destination[$cache->key()] = $cache;
      }
    }
  }

  /**
   * Get the existing words loaded by App.
   */
  public static function words () {
    if (!isset(static::$words)) static::loadWords();

    return static::$words;
  }

  /**
   * Load up all the WordsData from JSON files into Cache.
   */
  protected static function loadWords () {
    // Load JSON files and store words into the Cache for each file.
    static::loadCacheData("words", static::$words, "\Agnate\LazyQuest\Data\WordsData");
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
    // Load JSON files and store randomization tokens into the Cache for each file.
    static::loadCacheData("tokens", static::$tokens, "\Agnate\LazyQuest\Data\TokenData");
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
    // Load JSON files and store randomization formats into the Cache for each file.
    static::loadCacheData("formats", static::$formats, "\Agnate\LazyQuest\Data\FormatData");
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

  /**
   * Get the time as a micro amount and float.
   * @return float Returns the microtime as a float.
   */
  public static function microtimeFloat () {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
  }

  /**
   * Get the absolute server directory for this path.
   * @param string $path The path to look for. Example: `/sprites/raw`
   * @param boolean $public Whether or not the file is in the public directory.
   * @return string Returns the absolute server directory path.
   */
  public static function getPath ($path, $public = FALSE) {
    // Add the directory separator if it doesn't exist.
    if (substr($path, 0, 1) != DIRECTORY_SEPARATOR) $path = DIRECTORY_SEPARATOR . $path;
    // If it's a public path, use the public directory, otherwise use the game server root.
    return ($public ? GAME_SERVER_PUBLIC_DIR : GAME_SERVER_ROOT) . $path;
  }

  /**
   * Get the public URL for a path. Public means that the general population can access
   * this file (if it exists).
   * @param string $path The path to look for. Example: `/images/map.png`
   * @return string Returns the public URL for the path.
   */
  public static function getPublicUrl ($path) {
    // Strip out the public path if it exists.
    if (strpos($path, GAME_SERVER_PUBLIC_DIR) === 0) $path = substr($path, strlen(GAME_SERVER_PUBLIC_DIR));
    return $path;
  }
}