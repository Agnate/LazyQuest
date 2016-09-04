<?php

use \Agnate\RPG\Team;
use \Agnate\RPG\Update\UpdateBase;

namespace Agnate\RPG\Update;

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
    $team_fields = array();
    $team_fields[] = "tid INT(11) UNSIGNED AUTO_INCREMENT";
    $team_fields[] = "team_id VARCHAR(255) NOT NULL";
    $team_fields[] = "team_name VARCHAR(255) NOT NULL";
    $team_fields[] = "bot_user_id VARCHAR(255) NOT NULL";
    $team_fields[] = "bot_access_token VARCHAR(255) NOT NULL";
    $queries[] = static::createTableStatement(\Agnate\RPG\Team::$db_table, \Agnate\RPG\Team::$primary_key, $team_fields);

    return $queries;
  }

}