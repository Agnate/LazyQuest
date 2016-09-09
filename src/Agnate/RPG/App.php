<?php

namespace Agnate\RPG;

class App {

  protected static $started = FALSE;
  protected static $database;
  

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

  /**
   * Start the App and initialize the Database.
   */
  public static function start () {
    if (static::$started) return static::$started;

    // Create the database connection if it's not there.
    if (empty(static::$database)) {
      static::$database = new Database (DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }

    // Set App to started.
    static::$started = TRUE;

    return static::$started;
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