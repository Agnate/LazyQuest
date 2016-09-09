<?php

namespace Agnate\LazyQuest\Update;

use \Agnate\LazyQuest\Guild;
use \Agnate\LazyQuest\Team;

class Update_0_0_1 extends UpdateBase {

  static $version = '0.0.1';

  /**
   * Run any update scripts for this version.
   * @param $forced Boolean indicating if the update is forced to run
   *    even when database is at the same version number.
   * @return Array Return an array of UpdateQuery objects to run.
   */
  public static function run($forced = TRUE) {
    $queries = array();

    // Create tables for Team.
    $fields = array();
    $fields[] = "tid INT(11) UNSIGNED AUTO_INCREMENT";
    $fields[] = "team_id VARCHAR(255) NOT NULL";
    $fields[] = "team_name VARCHAR(255) NOT NULL";
    $fields[] = "bot_user_id VARCHAR(255) NOT NULL";
    $fields[] = "bot_access_token VARCHAR(255) NOT NULL";
    $queries[] = static::createTableStatement(Team::$db_table, Team::$primary_key, $fields);

    // Create tables for Guild.
    $fields = array();
    $fields[] = "gid INT(11) UNSIGNED AUTO_INCREMENT";
    $fields[] = "slack_id VARCHAR(255) NOT NULL";
    $fields[] = "name VARCHAR(255) NOT NULL";
    $fields[] = "icon VARCHAR(255) NOT NULL";
    $fields[] = "team_id INT(11) NOT NULL";
    $queries[] = static::createTableStatement(Guild::$db_table, Guild::$primary_key, $fields);

    // Create tables for Season.
    $fields = array();
    $fields[] = "sid INT(11) UNSIGNED AUTO_INCREMENT";
    $fields[] = "created INT(11) NOT NULL";
    $fields[] = "duration INT(11) NOT NULL";
    $fields[] = "active TINYINT(1) NOT NULL";
    $queries[] = static::createTableStatement(Season::$db_table, Season::$primary_key, $fields);

    return $queries;
  }

}