<?php

namespace Agnate\LazyQuest;

class Season extends Entity {

  public $sid;
  public $created;
  public $duration;
  public $active;

  protected $_map;
  
  // Static vars
  static $db_table = 'seasons';
  static $default_class = '\Agnate\LazyQuest\Season';
  static $primary_key = 'sid';
  static $partials = array();
  static $relationships = array();
  static $fields_int = array('created', 'duration');
  static $fields_array = array();


  /**
   * Load the map for a specific team.
   * @param $team The Team to load the map for.
   */
  public function loadMap (Team $team) {
    if (empty($team)) return FALSE;
    $this->_map = Map::load(array('season' => $this->sid, 'team_id' => $team->tid));
    return $this->_map;
  }

  /**
   * Get the Map for this Season.
   */
  public function map (Team $team) {
    if (empty($this->_map)) return $this->loadMap($team);
    return $this->_map;
  }


  /* =================================
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  ==================================== */

  /**
   * Get the current season.
   * @return Season Returns a Season instance of the current active Season.
   */
  public static function current () {
    return Season::load(array('active' => TRUE));
  }

}