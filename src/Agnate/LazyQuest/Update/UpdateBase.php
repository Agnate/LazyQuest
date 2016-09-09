<?php

namespace Agnate\LazyQuest\Update;

class UpdateBase {

  // Increment this for each new Update version.
  static $version = '0.0.0';

  const MINUTES = 60;
  const HOURS = 3600;
  const DAYS = 86400;

  protected function __construct() {}

  /**
   * Run any update scripts for this version.
   * @param $forced Boolean indicating if the update is forced to run
   *    even when database is at the same version number.
   * @return Array Return an array of UpdateQuery objects to run.
   */
  public static function run($forced = TRUE) {
    return array();
  }

  /**
   * Create a database table of fields.
   * @param $table_name Name of the table to create as a string.
   * @param $primary_key Name of the primary key to assign to the table.
   * @param $fields Array of field definitions. Example: "uid INT(11) UNSIGNED AUTO_INCREMENT"
   * @return Boolean Whether or not the query was successful.
   */
  public static function createTableStatement($table_name, $primary_key, Array $fields) {
    $fields[] = "PRIMARY KEY ( " . $primary_key . " )";
    return new UpdateQuery ("CREATE TABLE IF NOT EXISTS " . $table_name . " (" . implode(', ', $fields) . ")", array());
  }

}