<?php

namespace Agnate\LazyQuest;

class ActionChain extends EntityBasic {

  public $actions; // Array of ActionLink instances.

  const SEPARATOR = '__';

  static $fields_int;
  static $fields_array = array('actions');

  /**
   * Implements __clone().
   */
  public function __clone () {
    // Clone ActionLink instances.
    if (is_array($this->actions)) {
      foreach ($this->actions as &$action_link) {
        $action_link = clone $action_link;
      }
    }
  }

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
   * Add a sub-action to an action (defaults to current action). This will overwrite any existing subaction on the action.
   * @param $subaction String sub-action to store on the action.
   * @param $options Array of options to set on the action.
   * @param $action ActionLink instance of the action to alter.
   */
  public function alterActionLink ($subaction, $options = NULL, $action = NULL) {
    if (empty($action)) $action = $this->currentAction();
    $action->alter($subaction, $options);
  }

  /**
   * Return a clone of this ActionChain and remove the previous ActionLink.
   */
  public function goBack () {
    $chain = clone $this;
    array_pop($chain->actions);
    return $chain;
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
   * @param $args Can take one of many kinds of arguments:
   *    String - convert an action String into a new ActionChain by decoding it.
   *    Array - create from an Array of ActionLink instances.
   *    ActionLink - take all arguments (must all be ActionLink instances) and create a new ActionChain.
   * @return ActionChain Returns a new instance of ActionChain.
   */
  public static function create () {
    $args = func_get_args();
    $count = count($args);

    // Nothing? Return empty chain.
    if ($count <= 0)
      return new ActionChain;

    // If this is a String, decode.
    if ($count == 1) {
      if (is_string($args[0])) {
        return new ActionChain (array('actions' => static::decode($args[0])));
      }
      else if (is_array($args[0])) {
        // Only allow instances of ActionLink.
        $array = array_filter($args[0], array('Agnate\LazyQuest\ActionChain', 'filterByActionLink'));
        return new ActionChain (array('actions' => $array));
      }
    }

    // Check that the rest are ActionLink instances.
    $links = array_filter($args, array('Agnate\LazyQuest\ActionChain', 'filterByActionLink'));
    return new ActionChain (array('actions' => $links));
  }

  /**
   * An array_filter to filter by instances of ActionLink.
   */
  protected static function filterByActionLink ($elem) {
    return ($elem instanceof ActionLink);
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