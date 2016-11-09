<?php

namespace Agnate\LazyQuest\Update;

use Agnate\LazyQuest\ActionState;
use Agnate\LazyQuest\Guild;
use Agnate\LazyQuest\Season;
use Agnate\LazyQuest\Team;
use Agnate\LazyQuest\Map;
use Agnate\LazyQuest\Location;

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

    // Create table for Team.
    $fields = array();
    $fields[] = "tid INT(11) UNSIGNED AUTO_INCREMENT";
    $fields[] = "team_id VARCHAR(255) NOT NULL";
    $fields[] = "team_name VARCHAR(255) NOT NULL";
    $fields[] = "bot_user_id VARCHAR(255) NOT NULL";
    $fields[] = "bot_access_token VARCHAR(255) NOT NULL";
    $queries[] = static::createTableStatement(Team::$db_table, Team::$primary_key, $fields);

    // Create table for Guild.
    $fields = array();
    $fields[] = "gid INT(11) UNSIGNED AUTO_INCREMENT";
    $fields[] = "slack_id VARCHAR(255) NOT NULL";
    $fields[] = "username VARCHAR(255) NOT NULL";
    $fields[] = "name VARCHAR(255) NOT NULL";
    $fields[] = "icon VARCHAR(255) NOT NULL";
    $fields[] = "team_id INT(11) NOT NULL";
    $queries[] = static::createTableStatement(Guild::$db_table, Guild::$primary_key, $fields);

    // Create table for Season.
    $fields = array();
    $fields[] = "sid INT(11) UNSIGNED AUTO_INCREMENT";
    $fields[] = "created INT(11) NOT NULL";
    $fields[] = "active TINYINT(1) NOT NULL";
    $fields[] = "team_id INT(11) NOT NULL";
    $queries[] = static::createTableStatement(Season::$db_table, Season::$primary_key, $fields);

    // Create table for ActionState.
    $fields = array();
    $fields[] = "asid INT(11) UNSIGNED AUTO_INCREMENT";
    $fields[] = "team_id VARCHAR(255) NOT NULL";
    $fields[] = "guild_id VARCHAR(255) NOT NULL";
    $fields[] = "slack_id VARCHAR(255) NOT NULL";
    $fields[] = "timestamp VARCHAR(255) NOT NULL"; // Slack timestamps are different, so save as string.
    $fields[] = "channel_id VARCHAR(255) NOT NULL";
    $fields[] = "original_message LONGTEXT NOT NULL";
    $fields[] = "action VARCHAR(255) NOT NULL";
    $fields[] = "step VARCHAR(255) NOT NULL";
    $fields[] = "extra LONGTEXT NOT NULL";
    $queries[] = static::createTableStatement(ActionState::$db_table, ActionState::$primary_key, $fields);

    // Create table for Map.
    $fields = array();
    $fields[] = "mapid INT(11) UNSIGNED AUTO_INCREMENT";
    $fields[] = "season_id INT(10) UNSIGNED NOT NULL";
    $fields[] = "created INT(10) UNSIGNED NOT NULL";
    $queries[] = static::createTableStatement(Map::$db_table, Map::$primary_key, $fields);

    // Create table for Location.
    $fields = array();
    $fields[] = "locid INT(11) UNSIGNED AUTO_INCREMENT";
    $fields[] = "map_id INT(11) UNSIGNED NOT NULL";
    $fields[] = "team_id INT(11) UNSIGNED NOT NULL";
    $fields[] = "guild_id INT(11) UNSIGNED NOT NULL";
    $fields[] = "name VARCHAR(255) NOT NULL";
    $fields[] = "row INT(10) UNSIGNED NOT NULL";
    $fields[] = "col INT(10) UNSIGNED NOT NULL";
    $fields[] = "type VARCHAR(255) NOT NULL";
    $fields[] = "created INT(10) UNSIGNED NOT NULL";
    $fields[] = "revealed TINYINT(1) NOT NULL";
    $fields[] = "open TINYINT(1) NOT NULL";
    $fields[] = "star_min INT(10) UNSIGNED NOT NULL";
    $fields[] = "star_max INT(10) UNSIGNED NOT NULL";
    $fields[] = "keywords VARCHAR(255) NOT NULL";
    $fields[] = "map_icon VARCHAR(255) NOT NULL";
    $queries[] = static::createTableStatement(Location::$db_table, Location::$primary_key, $fields);

    return $queries;
  }

}