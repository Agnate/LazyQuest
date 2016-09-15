<?php

namespace Agnate\LazyQuest;

class ActionChain extends EntityBasic {

  public $action;

  /**
   * Construct the entity and set data inside.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class.
   */
  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);

    // Segment the action into an Array.
    $this->action = $this->decode($this->action);
  }

  /**
   * Get the current action requested by the user. Typically this is what is used to test Triggers against.
   * @return Array Return the last item of the action Array which is the action requested by the user.
   */
  public function currentAction () {
    if (empty($this->action)) return FALSE;
    return end($this->action);
  }

  /**
   * Get the current action requested by the user. Typically this is what is used to test Triggers against.
   * @return String Return the last item of the action Array which is the action requested by the user.
   */
  public function currentActionName () {
    if (empty($this->action)) return FALSE;
    $action = end($this->action);
    return count($action) > 0 ? $action[0] : FALSE;
  }

  /**
   * Get the action one prior to the current action. Typically this is used to generate the Back button.
   * @return Array Return the second-last item of the action Array.
   */
  public function prevAction () {
    if (empty($this->action)) return FALSE;
    $count = count($this->action);
    if ($count <= 1) return FALSE;
    return $this->action[$count - 1];
  }

  /**
   * Get the action one prior to the current action. Typically this is used to generate the Back button.
   * @return String Return the second-last item of the action Array.
   */
  public function prevActionName () {
    if (empty($this->action)) return FALSE;
    $count = count($this->action);
    if ($count <= 1) return FALSE;
    $action = $this->action[$count - 1];
    return count($action) > 0 ? $action[0] : FALSE;
  }

  /**
   * Encode the action Array into a String.
   * @return String Returns the encoded action Array as a String.s
   */
  public function encoded () {
    return $this->encode($this->action);
  }

  /**
   * Convert a string action into an associative Array.
   * @param $action The String action to decode into an Array.
   * @return Array Returns the decoded action.
   */
  public function decode ($action) {
    if (!is_string($action)) {
      if (is_array($action)) return $action;
      return array();
    }

    // Split by the primary separator.
    $decoded = explode('__', $action);

    // Check if the subaction needs any decoding.
    foreach ($decoded as $key => $subaction) {
      $decoded[$key] = explode('_', $subaction);
      
      // Check for third-level subaction.
      foreach ($decoded[$key] as $optionskey => $options) {
        $list = explode(',', $options);
        if (count($list) <= 1) continue;
        $decoded[$key][$optionskey] = $list;
      }
    }

    return $decoded;
  }

  /**
   * Convert an action Array into a String.
   * @param $action The Array action to encode into a String.
   * @return String Returns the encoded action.
   */
  public function encode ($action) {
    if (!is_array($action)) return '';

    // Check if the subaction needs any decoding.
    foreach ($action as $key => &$subaction) {
      if (!is_array($subaction)) continue;
      // Check for third-level subaction.
      foreach ($subaction as $optionskey => $list) {
        if (!is_array($list)) continue;
        $subaction[$optionskey] = implode(',', $list);
      }

      $action[$key] = implode('_', $subaction);
    }

    // Split by the primary separator.
    return implode('__', $action);
  }

  /* =================================
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  ==================================== */

  /**
   * Create a new ActionChain from a String.
   * @param $action String or Array action to convert to an ActionChain.
   * @return ActionChain Returns a new instance of ActionChain.
   */
  public static function create ($action) {
    return new ActionChain (array('action' => $action));
  }
}