<?php

use \Agnate\RPG\App;
use \Agnate\RPG\Database;

namespace Agnate\RPG;

class Entity extends EntityBasic {

  // Static vars
  static $db_table = '';
  static $default_class = '';
  static $partials = array();
  static $primary_key = '';
  static $relationships = array();

  /**
   * Load the related Entity based on the ID.
   * @param $property_name The name of the property on this Entity containing the ID.
   * @return Entity Returns the Entity loaded from the ID of the $property_name. Returns FALSE if none was found.
   */
  public function loadRelationship($property_name) {
    if (empty(static::$relationships[$property_name])) return FALSE;

    // Pull the class name from the relationship array.
    $class_name = static::$relationships[$property_name];
    if (!class_exists($class_name)) return FALSE;

    // Load the Entity.
    return $class_name::load(array($class_name::$primary_key => $this->{$property_name}));
  }

  /**
   * Loads one row of data based on the query $data provided.
   * @param $data An array of exact values that the query will search for.
   * @param $find_partials Boolean whether or not to search for partial matches. Will only search partials on field names defined in static::$partials.
   * @return EntityDB Returns an entity typed as the class calling it. (Example: Guild::load() will return a Guild entity). Returns FALSE if nothing was found.
   */
  public static function load($data, $find_partials = FALSE, $load_relationships = FALSE) {
    // If we don't have a database table, we're done.
    if (empty(static::$db_table)) return FALSE;

    // Generate the database tokens.
    $tokens = array();
    $new_data = array();
    foreach ($data as $key => &$value) {
      if (is_array($value)) {
        $tokens[$key] = array();
        $count = 0;
        foreach ($value as $subvalue) {
          $count++;
          $tokens[$key][] = ':' . $key . '_i' . $count;
          $new_data[':' . $key . '_i' . $count] = $subvalue;
        }
      }
      else {
        $tokens[$key] = ':' . $key;
        $new_data[':' . $key] = ($find_partials && in_array($key, static::$partials)) ? '%' . $value . '%' : $value;  
      } 
    }

    // Generate the WHERE statement based on tokens above.
    $where = array();
    foreach ($tokens as $key => $token) {
      // If this is an array of tokens, put it into an IN statement.
      if (is_array($token)) $where[] = $key . ' IN (' . implode(',', $token) . ')';
      // Else if need to look up partials, do that.
      else if ($find_partials && in_array($key, static::$partials)) $where[] = $key . ' LIKE ' . $token;
      // Otherwise just find the value given.
      else $where[] = $key . '=' . $token;
    }

    // If there is no WHERE entries, return FALSE since we need at least 1 filtering criteria.
    if (count($where) <= 0) return FALSE;

    // Fetch the rows.
    $query = "SELECT * FROM " . static::$db_table . " WHERE " . implode(' AND ', $where) . " LIMIT 1";
    $query = App::query($query);

    // Set the default class.
    if (static::$default_class != '' && class_exists(static::$default_class)) {
      $query->setFetchMode(\PDO::FETCH_CLASS, static::$default_class, array());
    }
    
    $query->execute($new_data);

    if ($query->rowCount() <= 0) return FALSE;

    $row = $query->fetch();

    return $row;
  }

  /**
   * Loads multiple rows of data based on the query $data provided.
   * @param $data An array of exact values that the query will search for.
   * @param $special The text here is tacted onto the end of the query. It's useful for things like "order by" and "limit".
   * @return Array Returns an array of entities typed as the class calling it. (Example: Guild::load_multiple() will return an array of Guild entities).
   */
  public static function load_multiple ($data, $special = "") {
    // If we don't have a database table, we're done.
    if (empty(static::$db_table)) return FALSE;

    // Generate the database tokens.
    $tokens = array();
    $new_data = array();
    foreach ($data as $key => &$value) {
      if (is_array($value)) {
        $tokens[$key] = array();
        $count = 0;
        foreach ($value as $subvalue) {
          $count++;
          $tokens[$key][] = ':' . $key . '_i' . $count;
          $new_data[':' . $key . '_i' . $count] = $subvalue;
        }
      }
      else {
        $tokens[$key] = ':' . $key;
        $new_data[':' . $key] = $value;  
      } 
    }

    $where = array();
    foreach ($tokens as $key => $token) {
      if (is_array($token)) $where[] = $key . ' IN (' . implode(',', $token) . ')';
      else $where[] = $key . '=' . $token;
    }

    $query = "SELECT * FROM " . static::$db_table . (count($where) > 0 ? " WHERE " . implode(' AND ', $where) : "") . (!empty($special) ? " " . $special : "");
    $query = App::query($query);

    if (static::$default_class != '' && class_exists(static::$default_class)) {
      $query->setFetchMode(\PDO::FETCH_CLASS, static::$default_class, array());
    }
    
    $query->execute($new_data);

    $rows = array();
    if ($query->rowCount() > 0) {
      while ($row = $query->fetch()) {
        $rows[] = $row;
      }
    }

    return $rows;
  }

  /**
   * Save the entity to the database.
   * @return Int Returns the ID of the primary key when saved. FALSE if it was unsuccessful at saving.
   */
  public function save() {
    // If we don't have a database table, we're done.
    if (empty(static::$db_table)) return FALSE;
    if (empty(static::$primary_key)) return FALSE;

    // Get database values to save out.
    $data = call_user_func('get_object_vars', $this);

    // If there's no $pid, it means it's a new lifeform.
    $is_new = empty($data[static::$primary_key]);
    if ($is_new) {
      unset($data[static::$primary_key]);
    }

    // Generate the database tokens.
    $tokens = array();
    $new_data = array();
    foreach ($data as $key => &$value) {
      if ($value === NULL) continue;

      $tokens[$key] = ':' . $key;
      $new_data[':' . $key] = $value;
    }

    // New object
    if ($is_new) {
      $query = "INSERT INTO " . static::$db_table . " (" . implode(', ', array_keys($tokens)) . ") VALUES (" . implode(", ", array_values($tokens)) . ")";
      $query = App::query($query);
      $success = $query->execute($new_data);

      // TODO: Remove this debug code.
      // if (!$success) {
      //   d("INSERT INTO ". static::$db_table ." (". implode(', ', array_keys($tokens)) .") VALUES (". implode(", ", array_values($tokens)) .")");
      //   d($query->errorInfo());
      // }
      
      // Save the $primary_key.
      $this->{static::$primary_key} = App::database()->connection()->lastInsertId(static::$primary_key);
    }
    // Existing object
    else {
      $sets = array();
      foreach($tokens as $key => $token) {
        if ($key == static::$primary_key) continue;
        $sets[] = $key .'='. $token;
      }
      
      $query = "UPDATE " . static::$db_table . " SET " . implode(', ', $sets) . " WHERE " . static::$primary_key . "=" . $tokens[static::$primary_key];
      $query = App::query($query);
      $success = $query->execute($new_data);
    }

    return $success;
  }

  /**
   * Deletes the entity from the database.
   * @return Array Returns an array containing three keys:
   *    'success' Contains TRUE or FALSE
   *    'result' Contains the query execution's result
   *    'error' Contains any error messages received
   */
  public function delete() {
    // If we don't have a database table, we're done.
    if (empty(static::$db_table)) return FALSE;
    if (empty(static::$primary_key)) return FALSE;

    $data = array(
      ':primarykey' => $this->{static::$primary_key},
    );

    // Delete the entry based on the primary key.
    $query = "DELETE FROM " . static::$db_table . " WHERE " . static::$primary_key . "=:primarykey";
    $query = App::query($query);
    $result = $query->execute($data);

    $info = array(
      'success' => ($result !== FALSE),
      'result' => $result,
    );

    // If it was an error, return the error.
    if ($result === FALSE) {
      $info['error'] = $query->errorInfo();
    }

    return $info;
  }

}