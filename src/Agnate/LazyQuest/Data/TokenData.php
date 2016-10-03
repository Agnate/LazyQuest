<?php

namespace Agnate\LazyQuest\Data;

use \Agnate\LazyQuest\App;

class TokenData {

  public $key;
  public $join;
  public $parts;

  protected $_original; // Contains an instance of TokenData with original data.

  // Keys to exclude when storing in cache.
  static $exclude = array('key');

  /**
   * Create token data to store and fetch from cache.
   * @param string $key Key used to store token data in the cache.
   * @param Array $data The data for this token. If not provided, it will be loaded from the cache.
   */
  function __construct ($key, $data = NULL) {
    // Set the key for this Token.
    $this->key = $key;

    // If default data isn't provided, load it from 
    if (!is_array($data) || empty($data)) $this->load();
    else $this->extract($data);
  }

  /**
   * Set everything to have default values where applicable.
   */
  protected function setDefaults () {
    $this->join = '';
    $this->parts = array();
  }

  /**
   * Save to cache.
   * @return boolean Returns TRUE if successfully saved, FALSE otherwise.
   */
  public function save () {
    // Convert to array for storage.
    return App::cache()->save($this->key, $this->compact());
  }

  /**
   * Load from cache.
   * @return TokenData Returns this instance so further operations can be made.
   */
  public function load () {
    // Load data from cache.
    $data = App::cache()->load($this->key);

    // Set defaults.
    $this->setDefaults();

    // Extract data from data.
    if ($data !== FALSE) $this->extract($data);

    return $this;
  }

  /**
   * Load the original from cache.
   * @return TokenData Returns the instance of TokenData containing the original data.
   */
  public function original () {
    if (!isset($this->_original)) $this->_original = new TokenData (TokenData::originalKey($this->key));
    return $this->_original;
  }

  /**
   * Extract data from Array into this object.
   * @param Array $data The data for this token.
   */
  public function extract (Array $data) {
    // Set the defaults.
    $this->join = '';
    $this->parts = array();

    // Pull from the data array based on key.
    foreach ($data as $key => $value) {
      if (property_exists($this, $key)) $this->{$key} = $value;
    }
  }

  /**
   * Compact the data for this token into an array.
   * @return Array Returns an associative array of the data for this token.
   */
  public function compact () {
    // Convert to array for storage.
    $data = call_user_func('get_object_vars', $this);

    // Remove excluded keys.
    foreach (static::$exclude as $key) {
      unset($data[$key]);
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
   * Get a random item from the list.
   * @param boolean $save Whether or not to save the TokenData after generating a random token.
   * @return string Returns the generated name based on the TokenData settings.
   */
  public function random ($save = TRUE) {
    $pieces = array();
    foreach ($this->parts as $key => &$piece) {
      // If the piece is a string, just use it.
      if (is_string($piece)) {
        $pieces[] = $piece;
        continue;
      }

      // If the piece is an array, we need to pick one from the list.
      if (is_array($piece)) {
        $index = array_rand($piece);
        
        // Re-index the list as it appears to be empty.
        if ($index === NULL) {
          // Load up the original to grab the parts.
          $piece = $original->parts[$key];
          $index = array_rand($piece);
        }

        $pieces[] = $piece[$index];
        unset($piece[$index]);
      }
    }

    // Save this TokenData if requested.
    if ($save) $this->save();

    return implode($this->join, $pieces);
  }


  /**
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  */

  /**
   * Get the name of the original key based on the current one.
   * @param string $key The current key.
   * @return string Returns the original key name.
   */
  public static function originalKey ($key) {
    return $key . '_ORIG';
  }

}