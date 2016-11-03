<?php

namespace Agnate\LazyQuest;

class Map extends Entity {

  public $mapid;
  public $season_id;
  public $created;

  protected $_locations;
  protected $_capital;

  static $db_table = 'maps';
  static $default_class = '\Agnate\LazyQuest\Map';
  static $partials = array();
  static $primary_key = 'mapid';
  static $relationships = array(
    'season_id' => '\Agnate\LazyQuest\Season',
  );
  static $fields_serialize;
  static $fields_json;
  static $fields_int = array('created');
  static $fields_array;

  const DENSITY = 0.15; // Percentage of Locations that are not empty.
  const CAPITAL_START_ROW = 100;
  const CAPITAL_START_COL = 81;
  const MIN_ROWS = 5; // 5
  const MIN_COLS = 5; // 5


  /**
   * Construct the entity and set data inside.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class.
   */
  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);

    // Add created timestamp if nothing did already.
    if (empty($this->created)) $this->created = time();
  }

  /**
   * Load all Location instances related to this Map.
   */
  public function loadLocations () {
    $this->_locations = Location::load_multiple(['mapid' => $this->mapid]);
  }

  /**
   * Get all Location instances related to this Map.
   * @return Array Returns a list of Location instances for this Map.
   */
  public function getLocations () {
    if (empty($this->_locations)) $this->loadLocations();
    return $this->_locations;
  }

  /**
   * Load the Location of the capital for this Map.
   */
  public function loadCapital () {
    $this->_capital = Location::load(['mapid' => $this->mapid, 'type' => Location::TYPE_CAPITAL]);
  }

  /**
   * Get the Location of the capital for this Map.
   * @return Location Returns a Location instance of the Map's capital.
   */
  public function getCapital () {
    if (empty($this->_capital)) $this->loadCapital();
    return $this->_capital;
  }

  /**
   * Generates a list of Location instances for this Map.
   * @param boolean $save_locations Whether or not to save the Location instances after creating.
   * @return Array Returns a list of Location instances generated for this Map.
   */
  public function generateLocations ($save_locations = TRUE) {
    $locations = array();

    $info = [
      'row_lo' => Map::CAPITAL_START_ROW - Map::MIN_ROWS,
      'row_hi' => Map::CAPITAL_START_ROW + Map::MIN_ROWS,
      'col_lo' => Map::CAPITAL_START_COL - Map::MIN_COLS,
      'col_hi' => Map::CAPITAL_START_COL + Map::MIN_COLS,
    ];

    $num_rows = ($info['row_hi'] - $info['row_lo'] + 1) + 1;
    $num_cols = ($info['col_hi'] - $info['col_lo'] + 1) + 1; // Letter
    $total = $num_rows * $num_cols;

    // Initialize the grid.
    $grid = array();
    $open = array();
    for ($r = $info['row_lo']; $r <= $info['row_hi']; $r++) {
      $grid[$r] = array();
      for ($c = $info['col_lo']; $c <= $info['col_hi']; $c++) {
        $grid[$r][$c] = NULL;
        $open[$r . '-' . $c] = ['row' => $r, 'col' => $c];
      }
    }

    // Create Capital somewhere in the middle.
    $capital_row = Map::CAPITAL_START_ROW;
    $capital_col = Map::CAPITAL_START_COL;

    $capital_data = [
      'mapid' => $this->mapid,
      'gid' => 0,
      'name' => 'The Capital',
      'row' => $capital_row,
      'col' => $capital_col,
      'type' => Location::TYPE_CAPITAL,
      'created' => time(),
      'revealed' => TRUE,
      'open' => TRUE,
    ];
    $capital = new Location ($capital_data);
    if ($save_locations) $capital->save();
    $grid[$capital_row][$capital_col] = $capital;
    $locations[] = $capital;
    unset($open[$capital_row . '-' . $capital_col]);
    $adjacents = array();

    // Loop through and create the rest of the Locations.
    $num_locs = ceil($total * Map::DENSITY);
    for ($i = 0; $i < $num_locs; $i++) {
      // Find an empty location.
      $open_index = array_rand($open);
      $coord = $open[$open_index];
      unset($open[$open_index]);
      // Generate the location.
      $location = Location::randomLocation($this, $coord['row'], $coord['col'], NULL, FALSE);
      // If we're not saving the location, it means we need to manually assign the star rating (so we can pass in the unsaved Capital).
      $location->assignStarRating($capital);
      if ($save_locations) $location->save();
      $grid[$coord['row']][$coord['col']] = $location;
      $locations[] = $location;
      // Hold onto locations adjacent to the capital.
      if ($capital->isAdjacent($location->row, $location->col)) $adjacents[] = $location;
    }

    // Fill the rest of the map with empty locations.
    foreach ($open as $coord) {
      // Generate the location.
      $location = Location::randomLocation($this, $coord['row'], $coord['col'], Location::TYPE_EMPTY, $save_locations);
      // if ($save_locations) $location->save();
      $grid[$coord['row']][$coord['col']] = $location;
      $locations[] = $location;
      // Hold onto locations adjacent to the capital.
      if ($capital->isAdjacent($location->row, $location->col)) $adjacents[] = $location;
    }

    // Mark the locations adjacent to the capital as open.
    foreach ($adjacents as $adjacent) {
      $adjacent->open = TRUE;
      if ($save_locations) $adjacent->save();
    }

    return $locations;
  }

}