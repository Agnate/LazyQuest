<?php

namespace Agnate\LazyQuest\Data;

use \Agnate\LazyQuest\App;

class CacheData {

  public $team; // Should contain the team Slack ID.
  public $key;
  public $raw;

  protected $_original; // Contains an instance of CacheData with original data.

  // Keys to exclude when storing in cache.
  static $exclude = array('team', 'key', 'raw');

  /**
   * Create cache data to store and fetch from cache.
   * @param string $team Team ID to store cache data against. For original data, leave blank (either NULL or empty string).
   * @param string $key Key used to store cache data in the cache.
   * @param * $data The data for this instance. If not provided, it will be loaded from the cache.
   */
  function __construct ($team, $key, $data = NULL) {
    // Set the key for this cache.
    $this->team = $team;
    $this->key = $key;

    // If default data isn't provided, load it from cache.
    if ($data === NULL) $this->load();
    else $this->extract($data);
  }

  /**
   * Get the cache key. This is a combination of the team slack ID and key.
   */
  public function key () {
    return static::getKey($this->team, $this->key);
  }

  /**
   * Set everything to have default values where applicable.
   */
  protected function setDefaults () {
    $this->raw = '';
  }

  /**
   * Save to cache.
   * @return boolean Returns TRUE if successfully saved, FALSE otherwise.
   */
  public function save () {
    // Convert to array for storage.
    return App::cache()->save($this->key(), $this->compact());
  }

  /**
   * Load from cache.
   * @return CacheData Returns this instance so further operations can be made.
   */
  public function load () {
    // Load data from cache.
    $data = App::cache()->load($this->key());
    if ($data === FALSE) $data = NULL;

    // Extract data from data.
    $this->extract($data);

    return $this;
  }

  /**
   * Load the original from cache.
   * @return CacheData Returns the instance of CacheData containing the original data.
   */
  public function original () {
    if (!isset($this->_original)) $this->_original = new static (NULL, $this->key);
    return $this->_original;
  }

  /**
   * Set the original (saves some processing if it was already created).
   * @param CacheData The original CacheData instance of this key.
   * @return boolean Returns TRUE if it was successful, FALSE otherwise.
   */
  public function setOriginal ($original) {
    // Make sure the keys match.
    if ($this->key != $original->key) return FALSE;

    $this->_original = $original;

    return TRUE;
  }

  /**
   * Extract data into this object.
   * @param * $data The data for this token.
   */
  public function extract ($data) {
    // Set the defaults.
    $this->setDefaults();

    // Set data variable.
    $this->raw = $data;

    // Pull from the data array based on key.
    if (is_array($data)) {
      foreach ($data as $key => $value) {
        if (property_exists($this, $key)) $this->{$key} = $value;
      }
    }
  }

  /**
   * Compact the data for this cache into an array.
   * @return Array Returns an associative array of the data for this cache.
   */
  public function compact () {
    // Convert to array for storage.
    $data = call_user_func('get_object_vars', $this);

    // Remove excluded keys.
    foreach (static::$exclude as $key) {
      unset($data[$key]);
    }

    // If $data is empty, it means there are no unique keys, so use $data proprety.
    if (empty($data)) {
      return $this->raw;
    }

    // Remove entries set to NULL.
    foreach ($data as $key => $value) {
      if ($value === NULL) unset($data[$key]);
    }

    return $data;
  }

  /**
   * Refresh the data from the original in cache.
   * @return boolean Returns TRUE if properly refreshed with all original data, FALSE otherwise.
   */
  public function refresh () {
    // Keep current data.
    $data = $this->compact();

    // Grab original data from cache and store it on this token.
    $this->extract($this->original()->compact());

    // Save it.
    $success = $this->save();

    // If it failed to save, revert the data back.
    if (!$success) $this->extract($data);

    return $success;
  }


  /**
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  */

  /**
   * Get the cache key.
   * @param string $team Team ID. For original data, leave empty (use either NULL or empty string).
   * @param string $key Key used to store cache data in the cache.
   * @return string Returns the cache key name for this team (or Original cache data if $team is empty).
   */
  public static function getKey ($team, $key) {
    return (!empty($team) ? $team . '_' : '') . $key;
  }

  /**
   * Check if this key and it's original key both have a Cache entry.
   * @param string $team Team ID. Use NULL or empty string to test Original cache.
   * @param string $key The key to check if a Cache exists for.
   * @return boolean Returns TRUE if the Cache exists, FALSE otherwise.
   */
  public static function isCached ($team, $key) {
    return (App::cache()->load(static::getKey($team, $key)) !== FALSE);
  }

  /**
   * Create a CacheData instance based on the original.
   * @param CacheData $original An instance of the original CacheData.
   * @param string $team Team ID.
   * @param boolean $save Whether or not to immediately save new cache instance. Defaults to TRUE.
   * @return CacheData Return an instance of CacheData that is a clone of its original.
   */
  public static function fromOriginal ($original, $team, $save = TRUE) {
    // Create instance.
    $instance = new static ($team, $original->key, $original->compact());
    $instance->setOriginal($original);
    if ($save) $instance->save();

    return $instance;
  }

  /**
   * Load CacheData from a JSON file.
   * @param string $team Team ID.
   * @param string $key The cache key.
   * @param string $filename The name of the local file to load. File MUST be in the GAME_SERVER_ROOT directory.
   * @param boolean $save Whether or not to immediately save new cache instance. Defaults to TRUE.
   * @return CacheData Return an instance of CacheData after loading original data from JSON file and saving to Cache.
   */
  public static function fromJsonFile ($team, $key, $filename, $save = TRUE) {
    // Load file.
    $data = static::loadJsonFile($filename);

    // Save to original.
    $original = new static (NULL, $key, $data);
    if ($save) $original->save();

    // Create team-specific instance if $team is given.
    if (empty($team)) return $original;
    else return static::fromOriginal($original, $team);
  }

  /**
   * Load up JSON file and return contents as associative Array.
   * @param string $filename The name of the local file to load. File MUST be in the GAME_SERVER_ROOT directory.
   * @return Array Returns JSON file data decoded into an associative Array.
   */
  protected static function loadJsonFile ($filename) {
    if (strpos($filename, GAME_SERVER_ROOT) !== 0) $filename = GAME_SERVER_ROOT . $filename;
    $json_string = file_get_contents($filename);
    return json_decode($json_string, TRUE);
  }

}