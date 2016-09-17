<?php

namespace Agnate\LazyQuest;

class ActionLink extends EntityBasic {

  public $action;
  public $subaction;
  public $options;


  const SEPARATOR = '|';
  const OPTION_SEPARATOR = ',';

  static $fields_int;
  static $fields_array = array('options');


  /**
   * Encode this ActionLink instance into a String.
   * @return String Returns this ActionLink instance encoded into a String.
   */
  public function encode () {
    $encoded = array();
    $encoded[] = $this->action;
    if (!empty($this->subaction)) $encoded[] = $this->subaction;
    if (!empty($this->options)) $encoded[] = implode(ActionLink::OPTION_SEPARATOR, $this->options);

    // Split by the primary separator.
    return implode(ActionLink::SEPARATOR, $encoded);
  }

  /**
   * Implement PHP toString function.
   */
  public function __toString () {
    return $this->encode();
  }


  /* =================================
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  ==================================== */

  /**
   * Create a new ActionLink from a String.
   * @param $action String or Array action to convert to an ActionLink.
   * @return ActionChain Returns a new instance of ActionLink.
   */
  public static function create ($action) {
    return new ActionLink (static::decode($action));
  }

  /**
   * Decode a string action and save data into this ActionLink instance. Vertical bars and commas are separators.
   * NOTE: If you want to include options, you MUST specify a subaction, even if it's empty.
   * Format:
   *    "action|subaction|opt1,opt2,opt3"
   *
   * @param $action_string The String action to decode into an Array.
   * @return Array Returns the decoded action.
   */
  public static function decode ($action_string) {
    // Check if the subaction needs any decoding.
    $decoded = explode(ActionLink::SEPARATOR, $action_string);

    // Put data in appropriate place.
    $action = array(
      'action' => '',
      'subaction' => '',
    );
    if (isset($decoded[0])) $action['action'] = $decoded[0];
    if (isset($decoded[1])) $action['subaction'] = $decoded[1];
    if (!empty($decoded[2])) $action['options'] = explode(ActionLink::OPTION_SEPARATOR, $decoded[2]);

    return $action;
  }

}