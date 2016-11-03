<?php

namespace Agnate\LazyQuest;

use \Agnate\LazyQuest\App;
use \Agnate\LazyQuest\Location;
use \Agnate\LazyQuest\Map;
use \Agnate\LazyQuest\Image\MapImage;

class Season extends Entity {

  public $sid;
  public $created;
  public $active;
  public $team_id;

  protected $_map;
  
  // Static vars
  static $db_table = 'seasons';
  static $default_class = '\Agnate\LazyQuest\Season';
  static $primary_key = 'sid';
  static $partials = array();
  static $relationships = array(
    'team_id' => '\Agnate\LazyQuest\Team',
  );
  static $fields_int = array('created');
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
   * Get the current Season.
   * @param Team $team The Team to get the current Season for.
   * @return Season Returns a Season instance of the current active Season.
   */
  public static function current ($team) {
    return Season::load(['active' => TRUE, 'team_id' => $team->tid]);
  }

  /**
   * Start a new season for a team.
   * @param Team $team The team to start a new season for.
   * @return Season Returns the Season instance if a new season was successfully created, FALSE otherwise.
   */
  public static function startNewSeason ($team) {
    // TODO: Remove old season information.
    // TODO: Remove all queues, action states, etc. for team.

    $time = time();
    // $hours = 60 * 60;
    // $days = $hours * 24;

    // Start new Season instance.
    $season = new Season ([
      'created' => $time,
      'active' => FALSE,
      'team_id' => $team->tid,
    ]);

    if (!($season->save())) {
      App::logger()->notice('Season::startNewSeason > Failed to save new Season for Team ' . $team->team_id);
      return FALSE;
    }

    // Create new Map instance.
    $map = new Map ([
      'season' => $season->sid,
      'created' => $time,
    ]);

    if (!($map->save())) {
      App::logger()->notice('Season::startNewSeason > Failed to save new Map for Team ' . $team->team_id);
      return FALSE;
    }

    // Generate Map locations.
    $locations = $map->generateLocations();

    // Activate Season.
    $season->active = TRUE;
    $season->save();

    if (!($season->save())) {
      App::logger()->notice('Season::startNewSeason > Failed to activate Season for Team ' . $team->team_id);
      return FALSE;
    }

    // Generate initial MapImage.
    $mapimage = MapImage::generateImage($map);

    // Looks like we're done, so return the Season created.
    return $season;
  }

}