<?php

namespace Agnate\LazyQuest;

use \Exception;
use \JsonSerializable;

class EntityBasic implements JsonSerializable {

  static $fields_int; // Array: any field keys set in this array will be automatically converted to an integer.
  static $fields_array; // Array: any field keys set in this array will be automatically set as an empty array if no data is present. 

  /**
   * Construct the entity and set data inside.
   * @param Array $data List of keyed values that are dynamically saved to the Entity if declared in the class.
   */
  function __construct($data = array()) {
    // Save values to object.
    if (count($data)) {
      foreach ($data as $key => $value) {
        if (property_exists($this, $key)) {
          $this->{$key} = $value;
        }
      }
    }

    // Set some more defaults.
    if (!empty(static::$fields_int)) {
      foreach (static::$fields_int as $field) {
        if (empty($this->{$field})) $this->{$field} = 0;
        else if (!empty($this->{$field}) && !is_int($this->{$field})) $this->{$field} = (int)$this->{$field};
      }
    }

    // Set some more defaults.
    if (!empty(static::$fields_array)) {
      foreach (static::$fields_array as $field) {
        if (!is_array($this->{$field})) {
          // If something other than an Array is here, throw an exception.
          if (!empty($this->{$field})) throw new Exception('Entity field ' . $field . ' expected an Array, ' . $this->{$field} . ' given.');
          else $this->{$field} = array();
        }
      }
    }
  }

  /**
   * Return a serializable array for use with JSON serializing.
   */
  public function jsonSerialize() {
    return call_user_func('get_object_vars', $this);
  }

  /**
   * Render a display name for this instance.
   * @param string $pattern The pattern to display.
   * @param Array $info List of key -> value pairs to replace in the pattern.
   * @return string Returns a string with items in the pattern replaced with data.
   */
  public function display ($pattern, $info) {
    // Create the token -> value pairs.
    $tokens = array();
    foreach ($info as $replace => $value) {
      $tokens['|%' . $replace . '%|'] = $replace;
    }
    // Replace single-letter tokens with more complex tokens.
    $tokened = str_replace(array_values($tokens), array_keys($tokens), $pattern);

    // Replace old single-letter tokens with actual values.
    foreach ($tokens as $key => $old_key) {
      $tokens[$key] = $info[$old_key];
    }

    // Replace tokens with actual values.
    return str_replace(array_keys($tokens), array_values($tokens), $tokened);
  }

}