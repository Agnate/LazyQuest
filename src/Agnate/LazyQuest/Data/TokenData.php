<?php

namespace Agnate\LazyQuest\Data;

use \Agnate\LazyQuest\App;

class TokenData extends CacheData {

  public $join;
  public $parts;

  /**
   * Set everything to have default values where applicable.
   */
  protected function setDefaults () {
    // Set parent defaults.
    parent::setDefaults();

    $this->join = '';
    $this->parts = array();
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
          $piece = $this->original()->parts[$key];
          $index = array_rand($piece);
        }

        // If we could not pick an array item, skip this part.
        if (empty($index) && $index !== 0) continue;

        // Select the piece we found.
        $pieces[] = $piece[$index];
        // Remove the piece so that it isn't repeated as often.
        unset($piece[$index]);
      }
    }

    // Save this TokenData if requested.
    if ($save) $this->save();

    return implode($this->join, $pieces);
  }

  /**
   * Convert a TokenData key into a string token for use by FormatData.
   * @return string Returns the key converted into a string token.
   */
  public function display () {
    return "[" . $this->key . "]";
  }

}