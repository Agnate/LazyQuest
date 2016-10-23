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
   * Get a RandomData instance of a random generated format from the list.
   * @param boolean $save Whether or not to save the FormatData after generating a random format.
   * @return RandomData Returns the generated format based on the FormatData settings.
   */
  public function random ($save = TRUE) {
    // Choose a format from the list.
    $index = array_rand($this->formats);
        
    // Re-index the list as it appears to be empty.
    if ($index === NULL) {
      // Load up the original to grab the formats.
      $this->formats = $this->original()->formats;
      // Find a new index.
      $index = array_rand($this->formats);
    }

    // If we could not pick an array item, we were unsuccessful.
    if (empty($index) && $index !== 0) return FALSE;

    // Select the format we found.
    $format = $this->formats[$index];
    // Remove the format so that it isn't repeated as often.
    unset($this->formats[$index]);

    // Get a list of tokens used in the format.
    $token_names = FormatData::getTokens($format);

    // Fetch the TokenData for the found tokens.
    $tokens = App::getTokens($token_names);

    // If we do not have the proper amount of tokens, set a logger error.
    if (count($token_names) != count($tokens)) {
      App::logger()->err("FormatData contains token but no TokenData exists.\nFormat used: " . $format . "\nTokens missing: [" . implode('], [', array_diff($token_names, array_keys($tokens))) . "]");
      return FALSE;
    }

    // Generate a random token for each TokenData.
    $replacements = array();
    foreach ($tokens as $token) {
      $replacements[$token->display()] = $token->random($save);
    }

    // Replace any tokens in the format.
    $replacement_keys = array_keys($replacements);
    $replacement_values = array_values($replacements);
    $text = str_replace($replacement_keys, $replacement_values, $format);
    $stripped = trim(str_replace($replacement_keys, '', $format));


    // Save this FormatData if requested.
    if ($save) $this->save();

    $data = new RandomData (array(
      'text' => $text,
      'tokens' => $replacement_values,
      'keywords' => array_merge($replacement_values, array($stripped)),
    ));

    return $data;
  }


  /**
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  */

  /**
   * Get a list of tokens used in a format.
   * @param string $format The format to look through for tokens.
   * @return Array Returns a list of token strings found in the format.
   */
  protected static function getTokens ($format) {
    if (!preg_match_all("/\[([^\]]*)\]+?/", $format, $matches)) return array();
    return $matches[1];
  }

}