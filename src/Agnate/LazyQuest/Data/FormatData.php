<?php

namespace Agnate\LazyQuest\Data;

use \Agnate\LazyQuest\App;

class FormatData extends CacheData {

  public $formats;

  /**
   * Set everything to have default values where applicable.
   */
  protected function setDefaults () {
    // Set parent defaults.
    parent::setDefaults();

    $this->formats = array();
  }

  /**
   * Get a random item from the list.
   * @param boolean $save Whether or not to save the TokenData after generating a random token.
   * @return string Returns the generated name based on the TokenData settings.
   */
  // public function random ($save = TRUE) {
  //   $pieces = array();
  //   foreach ($this->parts as $key => &$piece) {
  //     // If the piece is a string, just use it.
  //     if (is_string($piece)) {
  //       $pieces[] = $piece;
  //       continue;
  //     }

  //     // If the piece is an array, we need to pick one from the list.
  //     if (is_array($piece)) {
  //       $index = array_rand($piece);
        
  //       // Re-index the list as it appears to be empty.
  //       if ($index === NULL) {
  //         // Load up the original to grab the parts.
  //         $piece = $original->parts[$key];
  //         $index = array_rand($piece);
  //       }

  //       $pieces[] = $piece[$index];
  //       unset($piece[$index]);
  //     }
  //   }

  //   // Save this TokenData if requested.
  //   if ($save) $this->save();

  //   return implode($this->join, $pieces);
  // }

}