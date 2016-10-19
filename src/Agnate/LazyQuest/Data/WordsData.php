<?php

namespace Agnate\LazyQuest\Data;

class WordsData extends CacheData {

  public $words;

  /**
   * Set everything to have default values where applicable.
   */
  protected function setDefaults () {
    // Set parent defaults.
    parent::setDefaults();

    $this->words = array();
  }

  /**
   * Get a random item from the list.
   * @param boolean $save Whether or not to save the WordsData after generating a random token.
   * @return string Returns the generated name based on the WordsData settings.
   */
  public function random ($save = TRUE) {
    // If the words are not a list, return an empty string.
    if (!is_array($this->words)) return '';

    // Pick one from the list.
    $index = array_rand($this->words);
    
    // Re-index the list as it appears to be empty.
    if ($index === NULL) {
      // Refresh the words list.
      $this->refresh();
      if (!is_array($this->words) || count($this->words) <= 0) return '';
      // Pick from the list.
      $index = array_rand($this->words);
    }

    // If we could not pick an array item, return an empty string.
    if (empty($index) && $index !== 0) return '';

    // Select the word we found.
    $word = $this->words[$index];
    // Remove the word so that it isn't repeated as often.
    unset($this->words[$index]);

    // Save this TokenData if requested.
    if ($save) $this->save();

    return $word;
  }

  /**
   * Convert a TokenData key into a string token for use by FormatData.
   * @return string Returns the key converted into a string token.
   */
  public function display () {
    return "{" . $this->key . "}";
  }


  /**
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  */

  /**
   * Get the WordsData key based on the words token used. Example: {creature-adjectives}
   * @param string $token The token to convert into the key. Example: {creature-adjectives}
   */
  public static function getKeyFromToken ($token) {
    if (substr($token, 0, 1) != "{") return FALSE;
    if (substr($token, -1) != "}") return FALSE;
    return substr($token, 1, -1);
  }

}