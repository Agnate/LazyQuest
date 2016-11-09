<?php

namespace Agnate\LazyQuest;

use \Agnate\LazyQuest\Data\FormatData;
use \Agnate\LazyQuest\Data\RandomData;

class Location extends Entity {

  public $locid;
  public $map_id;
  public $team_id;
  public $guild_id; // Guild who revealed it.
  public $name;
  public $row;
  public $col;
  public $type;
  public $created;
  public $revealed;
  public $open;
  public $star_min;
  public $star_max;
  public $keywords;
  public $map_icon;

  protected $_map;
  protected $_keywords;

  static $db_table = 'locations';
  static $default_class = '\Agnate\LazyQuest\Location';
  static $partials = array();
  static $primary_key = 'locid';
  static $relationships = array(
    'map_id' => '\Agnate\LazyQuest\Map',
    'team_id' => '\Agnate\LazyQuest\Team',
    'guild_id' => '\Agnate\LazyQuest\Guild',
  );
  static $fields_serialize;
  static $fields_json;
  static $fields_int = array('created', 'row', 'col', 'star_min', 'star_max');
  static $fields_array;

  static $_types = array(Location::TYPE_DOMICILE, Location::TYPE_CREATURE, Location::TYPE_STRUCTURE, Location::TYPE_LANDMARK);

  // Used to calculate the exp/tile and represents the tile to travel 1 tile on the map.
  const TRAVEL_BASE_CALC_VALUE = 2700; // 2700 = 45 mins/tile (60 * 45)
  // This should always be equal to the TRAVEL_BASE_CALC_VALUE, but for debugging it can be lowered without affected the exp/tile.
  const TRAVEL_BASE = 2700;

  const TYPE_EMPTY = 'empty';
  const TYPE_CAPITAL = 'capital';
  const TYPE_DOMICILE = 'domicile';
  const TYPE_CREATURE = 'creature';
  const TYPE_STRUCTURE = 'structure';
  const TYPE_LANDMARK = 'landmark';

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
   * Adjust the data Array before it is saved to the database.
   * @param $data The data Array to be saved to the database. Includes all public-visible fields on this Entity.
   */
  public function beforeSave (&$data) {
    // Trigger default beforeSave() implementation.
    parent::beforeSave($data);

    // Encode the keywords.
    $data['keywords'] = $this->encodeKeywords($data['keywords']);
  }

  /**
   * This function triggers after the Entity is loaded from the database.
   * @param $entity The instance of the Entity to operate on.
   */
  public function afterLoad () {
    // Trigger default afterLoad() implementation.
    parent::afterLoad();

    // Decode the keywords.
    $this->keywords = $this->decodeKeywords($this->keywords);
  }

  /**
   * Render the coordinates and name of the Location.
   * @param string $pattern The pattern to display. Default pattern is "`C` N".
   *    Accepts any Slack markup and the following tokens:
   *    C - Coordinate name (example: A4)
   *    N - Location name, if available (empty locations have no name)
   */
  public function display ($pattern = "`C` N", $info = []) {
    // Set the replacement information.
    $info = array_merge($info, [
      'C' => $this->getCoordName(),
      'N' => $this->name,
    ]);

    return parent::display($pattern, $info);
  }

  /**
   * Render the coordinate name of the Location (example: A4).
   * @return string Returns the rendered coordinate name (example: A4).
   */
  public function getCoordName () {
    return Location::getLetter($this->col) .$this->row;
  }

  /**
   * Get the travel time (in the form of seconds) to get to this Location from the capital.
   * @param Bonus $bonus An instance of Bonus that may contain bonuses to travel time.
   * @return int Returns the travel time (in the form of seconds).
   */
  public function getDuration ($bonus = NULL) {
    // Calculate the raw distance and multiply by a time constant.
    $travel_speed_modifier = $this->calculateTravelSpeedModifier($bonus);
    $travel_per_tile = Location::TRAVEL_BASE * $travel_speed_modifier;
    return ceil($this->getDistance() * $travel_per_tile);
  }

  /**
   * Get the distance from this Location to the capital. Since Location instances are in a grid and a Location occupies one grid tile,
   * the distance is measured in the number of tiles between the two Location instances.
   * @param Location $capital The instance containing the capital if available (will be loaded it if not provided).
   * @return int Returns the distance between two Location instances.
   */
  public function getDistance ($capital = NULL) {
    if (empty($capital))  {
      // Get the map so we can find the town location.
      $map = $this->getMap();
      // Get the capital in the map.
      $capital = $map->getCapital();
    }

    // If we still don't have a capital, return 0.
    if (empty($capital)) return 0;

    return Location::calculateDistance($capital, $this);
  }

  /**
   * Check if there are any modifiers from the Bonus that apply to the travel speed.
   * @param Bonus $bonus An instance of Bonus that may contain bonuses to travel time.
   * @return float Returns the modifier to the travel speed (example: 0.95, which would speed up travel time by 5%).
   */
  public function calculateTravelSpeedModifier ($bonus = NULL) {
    if (empty($bonus)) return Bonus::DEFAULT_VALUE;
    $mod = $bonus->getMod(Bonus::TRAVEL_SPEED, $this);
    return $mod;
  }

  /**
   * Load the Map instance for this Location.
   * @return Map Returns the Map instance this Location is a part of.
   */
  public function getMap () {
    return $this->getRelationship('map_id');
  }

  /**
   * Populate a list of tokens with the actual values from this Location. Primarily used for quest name generation.
   * Token options:
   *
   *    !fullname -> The full name of the location.
   *    !creature -> The creature name (only for Creature locations).
   *    !creatureadj -> The adjective describing the creature name (only for Creature locations).
   *    !creaturefull -> The creature name including the adjective (only for Creature locations).
   *    !name -> The name of the domicile (only for Domicile locations).
   *    !dwelling -> The name of the dwelling/domicile (only for Domicile or Creature locations).
   *
   * @return Array Returns a list of token -> value pairs specific to this Location.
   */
  public function getTokensFromKeywords () {
    $tokens = ['!fullname' => $this->name];

    // Sift through the keywords if it's important.
    $keywords = $this->keywords;
    switch ($this->type) {
      case Location::TYPE_CREATURE:
        $tokens['!creature'] = $keywords[1];
        $tokens['!creatureadj'] = $keywords[0];
        $tokens['!creaturefull'] = $keywords[0] . ' ' . $keywords[1];
        $tokens['!dwelling'] = $keywords[2];
        break;

      case Location::TYPE_DOMICILE:
        $tokens['!name'] = $keywords[0];
        $tokens['!dwelling'] = $keywords[1];
        break;
    }

    return $tokens;
  }

  /**
   * Extract what category of icon we want to use on the map based on the keywords.
   * @return string Returns an icon name to represent this Location.
   */
  public function getMapIcon () {
    if ($this->type == Location::TYPE_CAPITAL) return 'capital';

    switch ($this->type) {
      default:
        // Take the last keyword, split by space and take the first word of the group.
        $pieces = explode(' ', array_pop($this->keywords));
        return strtolower(array_shift($pieces));
    }
  }

  /**
   * Get Location instances that are adjacent to this Location (North, South, East, and West).
   * @param Boolean $create_new Set to TRUE if you want to create new Location instances for NSEW spots that do not exist yet, FALSE otherwise.
   */
  public function getAdjacentLocations ($create_new = FALSE, $save_new = TRUE) {
    $row = $this->row;
    $col = $this->col;
    $locations = array();

    if ($create_new) $map = $this->getMap();

    // Get all locations to the NSEW of this one.
    $coords = [
      ['row' => $row - 1, 'col' => $col],
      ['row' => $row + 1, 'col' => $col],
      ['row' => $row, 'col' => $col - 1],
      ['row' => $row, 'col' => $col + 1],
    ];

    foreach ($coords as $coord) {
      if ($coord['row'] < 1 || $coord['row'] > 999) continue;
      if ($coord['col'] < 1 || $coord['col'] > 702) continue;

      $data = [
        'row' => $coord['row'],
        'col' => $coord['col'],
        'map_id' => $this->map_id,
      ];
      $location = Location::load($data);

      // If there's no location, create a new one or continue.
      if (empty($location)) {
        if (!$create_new) continue;
        // Use Map density to decide if it's empty or not.
        $type = Location::TYPE_EMPTY;
        // Generate a random non-empty type if we randomize the density and get a non-empty location.
        if (rand(0, 100) <= (Map::DENSITY * 100)) $type = NULL;
        $location = Location::randomLocation($map, $data['row'], $data['col'], $type, $save_new);
      }

      $locations[] = $location;
    }

    return $locations;
  }

  /**
   * Check if a (row, column) coordinate combination is adjacent to this Location.
   * @param int $row The row number to check.
   * @param int $col The column number to check.
   * @return Boolean Returns TRUE if the coordinate is adjacent, FALSE otherwise.
   */
  public function isAdjacent ($row, $col) {
    if (abs($this->row - $row) <= 1 && $this->col == $col) return TRUE;
    if (abs($this->col - $col) <= 1 && $this->row == $row) return TRUE;
    return FALSE;
  }

  /**
   * Assign a star rating to this Location. Typically this is based on the distance from the capital.
   * @param Location $capital The Location instance for the capital (will be loaded if not provided).
   */
  public function assignStarRating ($capital = NULL) {
    // Assign star rating based on proximity to the Capital.
    if ($this->type != Location::TYPE_EMPTY) {
      $distance = $this->getDistance($capital);
      $this->star_max = Location::calcStarRating($distance);
      if ($this->star_max > 1) $this->star_min = $this->star_max - rand(0, 1);
      else $this->star_min = $this->star_max;
    }
  }

  /**
   * Get the amount of experience from exploring this Location.
   * @param Location $capital The instance containing the capital if available (will be loaded it if not provided).
   * @return int Returns the amount of experience points earned from exploring this Location.
   */
  public function getExplorationExp ($capital = NULL) {
    // Get the distance (aka number of tiles from the Capital).
    $distance = $this->getDistance($capital);
    // Use the distance to calculate the "difficulty" of the locations in this area.
    $star = Location::calcStarRating($distance);
    if ($star <= 0) return 0;

    // 1-star ->  6 exp/hour
    // 2-star ->  8 exp/hour
    // 3-star -> 10 exp/hour
    // 4-star -> 12 exp/hour
    // 5-star -> 14 exp/hour
    $rates = array(
      'star1' => 6,
      'star2' => 8,
      'star3' => 10,
      'star4' => 12,
      'star5' => 14,
    );

    // Adjust the exp/tile based on travel time and approximate difficulty.
    $hours_ratio = Location::TRAVEL_BASE_CALC_VALUE / (60 * 60);
    return ceil(($rates['star'.$star] * $hours_ratio) * $distance);
  }

  /**
   * Generate a name for this Location (based on its type).
   * @return RandomData Returns the RandomData generated for the name if successful, FALSE otherwise.
   */
  public function generateName () {
    // Empty locations are just blank.
    if ($this->type == Location::TYPE_EMPTY) return new RandomData (['text' => '']);

    // Get the FormatData based on Location $type.
    $team = $this->getRelationship('team_id');
    $format = new FormatData ($team->team_id, $this->formatKey());

    if (empty($format)) return FALSE;

    // Get a random name based on the format.
    $random = $format->random();

    if (empty($random)) return FALSE;

    // Update this Location with the information.
    $this->name = $random->text;
    $this->keywords = $random->keywords;

    return $random;
  }

  /**
   * Get the FormatData key based on this Location.
   */
  public function formatKey () {
    return 'location-' . $this->type;
  }

  /* =================================
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  ==================================== */

  /**
   * List of Location types.
   * @param boolean $include_empty Whether or not to include the TYPE_EMPTY in the list.
   */
  public static function types ($include_empty = FALSE) {
    return $include_empty ? array_merge(Location::$_types, array(Location::TYPE_EMPTY)) : Location::$_types;
  }

  /**
   * Convert the grid number into a letter.
   * @param int $num The number to convert into a letter.
   * @return string Returns the letter (or multiple letters) that represents this number.
   */
  public static function getLetter ($num) {
    if ($num > 26) {
      $first = floor($num / 26);
      $second = $num % 26;
      if ($second == 0) {
        $first--;
        $second = 26;
      }
      return chr(64 + $first) . chr(64 + $second);
    }

    return chr(64 + $num);
  }

  /**
   * Convert the grid letter into a number.
   * @param string $letter The letter to convert into a number.
   * @return int Returns the number that represents this letter.
   */
  public static function getNumber ($letter) {
    $letter = strtoupper($letter);
    if (strlen($letter) > 1) {
      // Separate letters.
      $first = substr($letter, 0, 1);
      $second = substr($letter, 1, 1);
      // Convert to numbers.
      $first = ord($first) - 64;
      $second = ord($second) - 64;
      // Reverse engineer.
      return ($first * 26) + $second;
    }
    return ord($letter) - 64;
  }

  /**
   * Generate a random Location instance.
   * @param Map $map The Map instance this Location will be plotted on.
   * @param int $row The row number on the map for this Location's coordinates.
   * @param int $col The column number on the map for this Location's coordinates.
   * @return Location Returns a new Location instance for the provided Map, row, and column.
   */
  public static function randomLocation (Map $map, $row, $col, $type = NULL, $save = TRUE) {
    // Randomize type.
    if (empty($type)) {
      $types = Location::types();
      $type = $types[array_rand($types)];
    }

    // Create location.
    $location = new Location ([
      'map_id' => $map->mapid,
      'team_id' => $map->getTeam()->tid,
      'guild_id' => 0,
      'row' => $row,
      'col' => $col,
      'type' => $type,
      'created' => time(),
      'revealed' => FALSE,
      'open' => FALSE,
    ]);

    // Generate the name.
    $location->generateName();

    // Assign star rating based on proximity to the Capital.
    $location->assignStarRating();

    if ($save) $location->save();

    return $location;
  }

  /**
   * Get all unique location names.
   * @param Team $team The team to get location names for.
   * @param boolean $revealed_only Whether or not to get only the revealed locations.
   */
  public static function getAllUniqueLocations ($team, $revealed_only = TRUE) {
    // Get the current season.
    $season = Season::current();
    if (empty($season)) return FALSE;
    $map = $season->getMap();
    
    // Get list of all locations.
    $types = Location::types();
    $data = ['mapid' => $map->mapid, 'type' => $types];
    if ($revealed_only) $data['revealed'] = TRUE;
    return Location::loadMultiple($data);
  }

  /**
   * Sort a list of Location instances based on star rating.
   * @param Array $locations A list of Location instances to sort.
   * @return Array Returns a list of sorted Location instances.
   */
  public static function sortLocationsByStar ($locations) {
    // Sort out locations by star-rating.
    $all_locations = array('all' => $locations);
    foreach ($locations as &$location) {
      for ($star = $location->star_min; $star <= $location->star_max; $star++) {
        if ($star == 0) continue;
        if (!isset($all_locations[$star])) $all_locations[$star] = array();
        $all_locations[$star]['loc'.$location->locid] = $location;
      }
    }

    return $all_locations;
  }

  /**
   * Calculate the distance between two Location instances. Since Location instances are in a grid and a Location occupies one grid tile,
   * the distance is measured in the number of tiles between the two Location instances.
   * @param Location $location1 The first Location instance.
   * @param Location $location2 The second Location instance.
   * @return int Returns the distance between two Location instances.
   */
  public static function calculateDistance (Location $location1, Location $location2) {
    return sqrt(pow(($location1->row - $location2->row), 2) + pow(($location1->col - $location2->col), 2));
  }

  /**
   * Calculate the star rating based on the distance from the capital.
   * @param float $distance The number of grid tiles away from the capital to calculate from.
   * @return int Returns the star rating from 1 to 5.
   */
  public static function calcStarRating ($distance) {
    // Adjust the exp/tile based on travel time and approximate difficulty.
    $hours_ratio = Location::TRAVEL_BASE_CALC_VALUE / (60 * 60);

    // Hours of travel for this star rating.
    $rates = [
      '1' => 10.5,
      '2' => 18,
      '3' => 25.5,
      '4' => 33,
    ];

    if ($distance <= 0) return 0;

    // Only checks star ratings 1-4.
    foreach ($rates as $star => $hours) {
      if ($distance <= $rates[$star] / $hours_ratio) return intval($star);
    }

    // Was farther than 4-star rating, so it's a 5-star.
    return 5;
  }

  /**
   * Decode a keywords string into an Array.
   * @param string $string Encoded keywords string to decode into an Array.
   * @return Array Returns a list of keywords.
   */
  protected static function decodeKeywords ($string) {
    return empty($string) ? array() : explode('|', $string);
  }

  /**
   * Encode a keywords list into a string.
   * @param Array $list List of keywords.
   * @return string Returns a string-encoded keyword list.
   */
  protected static function encodeKeywords ($list) {
    return is_array($list) ? implode('|', $list) : '';
  }

}