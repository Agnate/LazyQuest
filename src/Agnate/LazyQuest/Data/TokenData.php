<?php

namespace Agnate\LazyQuest\Data;

use \Agnate\LazyQuest\App;

class TokenData extends CacheData {

  public $join;
  public $parts;

  /**
   * Clone object instances in the $parts list.
   */
  function __clone () {
    if (is_array($this->parts)) {
      foreach ($this->parts as $key => $part) {
        if (!is_object($part)) continue;
        $this->parts[$key] = clone $part;
      }
    }
  }

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
   * Extract data into this object.
   * @param * $data The data for this token.
   */
  public function extract ($data) {
    // Perform parent extract.
    parent::extract($data);

    // Get all words.
    $words = App::words();

    // Go through the parts and convert any WordsData items into a proper instance.
    foreach ($this->parts as $key => $part) {
      // We only convert string parts into WordsData.
      if (!is_string($part)) continue;

      // See if this string is a WordsData instance token.
      $words_key = WordsData::getKeyFromToken($part);
      // Not a token, so we can skip.
      if ($words_key === FALSE) continue;

      // If we can't find a WordsData instance, throw a logger error and replace with empty string.
      if (!isset($words[$words_key])) {
        App::logger()->err("TokenData contains words but no WordsData exists, replaced with empty string.\nTokenData key: " . $this->key . "\nWords missing: " . $part);
        $this->parts[$key] = '';
        continue;
      }

      // Set the WordsData based on the word key found.
      $this->parts[$key] = clone ($words[$words_key]);
    }
  }

  /**
   * Compact the data for this cache into an array.
   * @return Array Returns an associative array of the data for this cache.
   */
  public function compact () {
    // Run parent compact() function.
    $data = parent::compact();

    // Convert any WordsData entries back into a token.
    foreach ($data['parts'] as $key => $part) {
      if (!$part instanceof WordsData) continue;
      $data['parts'][$key] = $part->display();
    }

    return $data;
  }

  /**
   * Refresh one of the parts list.
   * @param int $index The index of the parts list to refresh.
   */
  public function refreshPart ($index) {
    // Perform a clone in case this is a WordsData instance.
    $parts = $this->original()->parts[$index];
    if (is_object($parts)) $parts = clone $parts;
    $this->parts[$index] = $parts;
    return $this->parts[$index];
  }

  /**
   * Get a random item from the list.
   * @param boolean $save Whether or not to save the TokenData after generating a random token.
   * @return string Returns the generated name based on the TokenData settings.
   */
  public function random ($save = TRUE) {
    $parts = array();
    foreach ($this->parts as $key => &$part) {
      // If the part is a string, just use it.
      if (is_string($part)) {
        $parts[] = $part;
        continue;
      }
      // If the part is a list of words, randomly pick one.
      else if ($part instanceof WordsData) {
        $parts[] = $part->random($save);
      }
      // If the part is an array, we need to pick one from the list.
      else if (is_array($part)) {
        $index = array_rand($part);
        
        // Re-index the list as it appears to be empty.
        if ($index === NULL) {
          // Refresh the individual part.
          $part = $this->refreshPart($key);
          $index = array_rand($part);
        }

        // If we could not pick an array item, skip this part.
        if (empty($index) && $index !== 0) continue;

        // Select the part we found.
        $parts[] = $part[$index];
        // Remove the part so that it isn't repeated as often.
        unset($part[$index]);
      }
    }

    // Save this TokenData if requested.
    if ($save) $this->save();

    return implode($this->join, $parts);
  }

  /**
   * Convert a TokenData key into a string token for use by FormatData.
   * @return string Returns the key converted into a string token.
   */
  public function display () {
    return "[" . $this->key . "]";
  }

}