<?php

namespace Agnate\LazyQuest\Randomizer;

class JsonRandomizerData implements RandomizerDataInterface {

  public $filename;
  public $original_filename;
  public $data;
  public $original_data;

  /**
   * Load data from a JSON file for randomization. JSON files should be stored at `/data/json` and original files stored at `/data/json/original`.
   * @param string $filename Location of the file from the game server's root where the file is stored.
   * @param string $original_filename Location of the original file from the game server's root where the file is stored.
   */
  function __construct ($filename, $original_filename) {
    $this->filename = $filename;
    $this->original_filename = $original_filename;

    // Load the data.
    $this->load();
    $this->loadOriginal();
  }

  /**
   * Load data in from the JSON file.
   * @return Array|stdClass Returns the data stored at $data on this instance.
   */
  public function load () {
    $this->data = $this->loadFile($this->filename);
    return $this->data;
  }

  /**
   * Load data in from the original JSON file.
   * @return Array|stdClass Returns the data stored at $data on this instance.
   */
  protected function loadOriginal () {
    $this->original_data = $this->loadFile($this->original_filename);
    return $this->original_data;
  }

  /**
   * Load the data from a file.
   * @param string $filename Location of the file from the game server's root where the file is stored.
   * @return Array|stdClass Returns the json_decode() contents of the file.
   */
  protected function loadFile ($filename) {
    $file_name = GAME_SERVER_ROOT . $filename;
    $json_string = file_get_contents($file_name);
    return json_decode($json_string, TRUE);
  }

  /**
   * Write out the JSON file to prevent names from being reused.
   */
  public function save () {
    return $this->saveFile($this->filename, $this->data);
  }

  /**
   * Write out the JSON file to prevent names from being reused.
   */
  protected function saveFile ($filename, $data) {
    $fp = fopen(GAME_SERVER_ROOT . $filename, 'w');
    $success = fwrite($fp, json_encode($data));
    fclose($fp);

    return ($success !== FALSE);
  }

  /**
   * Refresh the file from the original. This is typically done when a piece of the data has been emptied.
   */
  public function refresh () {
    // Copy over original data.
    $this->data = clone $this->original_data;

    // Overwrite the working copy with the new list.
    $this->saveFile($this->filename, $this->data);

    return $this->data;
  }

}