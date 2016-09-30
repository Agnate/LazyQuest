<?php

namespace Agnate\LazyQuest\Randomizer;

class Randomizer {

  public $randomizer_data; // Instance that implements RandomizerDataInterface.

  /**
   * Create a randomizing generator based on some kind of randomized data.
   * @param RandomizerDataInterface $data An instance of a class that implements RandomizerDataInterface. Currently only option is JsonRandomizerData. @see JsonRandomizerData
   */
  function __construct (RandomizerDataInterface $data) {
    $this->$randomizer_data = $data;
  }

  /**
   * Generate a randomized name from the data provided.
   * @param Array $default_tokens The default list of tokens to provided.
   * @param boolean $save Whether or not to save the randomizer data object after generating from it.
   * @return Array Returns a list with the generated data.
   */
  public function generate ($default_tokens = array(), $save = TRUE) {
    if (!is_array($default_tokens)) return FALSE;

    $info = [
      'name' => '',
      'keywords' => array(),
      'tokens' => $default_tokens,
    ];

    // Grab the data we need.
    $data = &$this->randomizer_data->data;
    $orig_data = $this->randomizer_data->original_data;

    // If it is format-based, pick a format and generate the pieces.
    if (isset($data['format'])) {
      $format_index = array_rand($data['format']);
      $format = $data['format'][$format_index];

      // Create the list of substitution tokens.
      $tokens = $default_tokens;
      foreach ($data as $token => &$data) {
        if ($token == 'format') continue;
        if (!isset($data['parts'])) continue;
        
        // Generate the token value.
        $join = isset($data['join']) ? $data['join'] : ' ';
        $parts = $this->generateFromParts($data['parts'], $orig_data[$token]['parts']);
        $tokens[$token] = implode($join, $parts);
      }

      $token_keys = array_keys($tokens);
      $info['keywords'] = array_values($tokens);
      $info['name'] = str_replace($token_keys, $info['keywords'], $format);

      // Add format to the keywords after the name replacement.
      $keyword = str_replace($token_keys, '', $format);
      $info['keywords'][] = trim($keyword);
      $info['tokens'] = $tokens;
    }
    // If it's just a series of parts, connect them.
    else if (isset($data['parts'])) {
      $join = isset($data['join']) ? $data['join'] : ' ';
      $info['keywords'] = $this->generateFromParts($data['parts'], $orig_data['parts']);
      $info['name'] = implode($join, $info['keywords']);
    }

    // If we need to save the removal of certain randomizer data, do so now.
    if ($save) {
      $this->randomizer_data->save();
    }

    return $info;
  }

  /**
   * Pick a bunch of parts randomly.
   */
  protected function generateFromParts (&$parts, $original_parts) {
    if (is_string($parts)) return $parts;
    if (!is_array($parts)) return '';

    // If there are arrays for each part, randomly pick one.
    $name = array();
    foreach ($parts as $key => &$list) {
      if (is_array($list)) {
        $index = array_rand($list);
        // Re-index the list as it appears to be empty.
        if ($index === NULL) {
          $list = $original_parts[$key];
          $index = array_rand($list);
        }
        $name[] = $list[$index];
        unset($list[$index]);
      }
      else if (is_string($list)) $name[] = $list;
    }

    return $name;
  }

}