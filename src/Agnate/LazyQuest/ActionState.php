<?php

namespace Agnate\LazyQuest;

class ActionState extends Entity {

  public $asid;
  public $slack_id;
  public $timestamp;
  public $action;
  public $extra;
  
  // Static vars
  static $db_table = 'action_states';
  static $default_class = '\Agnate\LazyQuest\ActionState';
  static $primary_key = 'asid';
  static $partials = array();
  static $relationships = array();
  static $fields_serialize = array('extra');
  static $fields_int = array('timestamp');
  static $fields_array = array();


  /* =================================
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  ==================================== */

  /**
   * Load the current ActionState for this Slack user. Sorts by the timestamp to get most recent if timestamp is not provided in $data parameter.
   * @param $data An array of exact values that the query will search for.
   * @return ActionState Returns an instance of ActionState if available.
   */
  public static function current ($data) {
    // Fetch the most recent entry if there's no timestamp.
    $special = empty($data['timestamp']) ? "ORDER BY timestamp DESC" : "";
    return ActionState::load($data, FALSE, FALSE, $special);
  }

}