<?php

namespace Agnate\LazyQuest;

class ActionChain extends EntityBasic {

  public $actions; // Array of ActionLink instances.

  const SEPARATOR = '__';

  static $fields_int;
  static $fields_array = array('actions');

  /**
   * Get the current action requested by the user. Typically this is what is used to test Triggers against.
   * @return Array Return the last item of the action Array which is the action requested by the user.
   */
  public function currentAction () {
    if (empty($this->actions)) return FALSE;
    return end($this->actions);
  }

  /**
   * Get the current action requested by the user. Typically this is what is used to test Triggers against.
   * @return String Return the last item of the action Array which is the action requested by the user.
   */
  public function currentActionName () {
    if (empty($this->actions)) return FALSE;
    $action = end($this->actions);
    return $action->action;
  }

  /**
   * Get the action one prior to the current action. Typically this is used to generate the Back button.
   * @return Array Return the second-last item of the action Array.
   */
  public function prevAction () {
    if (empty($this->actions)) return FALSE;
    $count = count($this->actions);
    if ($count <= 1) return FALSE;
    return $this->actions[$count - 2];
  }

  /**
   * Get the action one prior to the current action. Typically this is used to generate the Back button.
   * @return String Return the second-last item of the action Array.
   */
  public function prevActionName () {
    if (empty($this->actions)) return FALSE;
    $count = count($this->actions);
    if ($count <= 1) return FALSE;
    $action = $this->actions[$count - 2];
    return $action->action;
  }

  /**
   * Convert this ActionChain instance into a String.
   * @return String Returns this ActionChain instance encoded into a String.
   */
  public function encode () {
    // Encode all of the ActionLinks.
    $actions = array();
    foreach ($this->actions as $action) {
      if (!($action instanceof \Agnate\LazyQuest\ActionLink)) continue;
      $actions[] = $action->encode();
    }

    // Split by the primary separator.
    return implode(ActionChain::SEPARATOR, $actions);
  }

  /**
   * Clone this ActionChain and give it a new subaction.
   */
  // public function clone ($subaction = '', $options = '') {
  //   $chain = ActionChain::create($this->encoded());
  //   $last_action = $chain->action
  // }

  /**
   * Add a sub-action to an action. Defaults to the current action if none is specified.
   */
  // public function addSubAction ($subaction, $action_name = NULL) {
  //   $last_action = $this->prevAction();
  //   $last_action
  // }

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
    return new ActionChain (array('actions' => static::decode($action)));
  }

  /**
   * Decode an action string into a list of ActionLinks.
   */
  public static function decode ($action_string) {
    // Split up the action chain by the separator.
    $strings = explode(ActionChain::SEPARATOR, $action_string);

    // For each value, decode to an ActionLink.
    $actions = array();
    foreach ($strings as $value) {
      if (empty($value)) continue;
      $actions[] = ActionLink::create($value);
    }

    // Return the list of links.
    return $actions;
  }
}