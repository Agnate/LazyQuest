<?php

namespace Agnate\LazyQuest\Randomizer;

interface RandomizerDataInterface {

  /**
   * Load the data for this RandomizerData instance.
   * @return Array|stdClass Returns the full amount of randomized data.
   */
  public function load ();

  /**
   * Save the data for this RandomizerData instance.
   * @return boolean Returns TRUE if the data saved properly, FALSE otherwise.
   */
  public function save ();

  /**
   * Refresh the pool of data.
   */
  public function refresh ();

  /**
   * Create one randomized item from this RandomizerData instance.
   * @param Array $default_tokens List of default tokens to pull from.
   */
  public function generate ($default_tokens = array());

}