<?php

namespace Agnate\LazyQuest\Action;

use \Agnate\LazyQuest\EntityBasic;

class Step extends EntityBasic {

  public $name;
  public $type;
  public $function;

  const TYPE_ASK = 'ask';
  const TYPE_PROCESS = 'process';
  const TYPE_APPROVAL = 'approval';

  protected static $types = [Step::TYPE_ASK, Step::TYPE_PROCESS, Step::TYPE_APPROVAL];
  protected static $waits_for_input = [Step::TYPE_ASK, Step::TYPE_APPROVAL];


  /**
   * Construct the entity and set data inside.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class.
   */
  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);

    // Validate type.
    if (!$this->validType($this->type)) $this->type = static::TYPE_PROCESS;
  }

  /**
   * Check if a type is valid.
   * @param $type The type to check. Should be one of the TYPE constants defined in Step class.
   * @return Boolean Returns TRUE if the type is valid, FALSE otherwise.
   */
  public function validType ($type) {
    return in_array($type, static::$types);
  }

  /**
   * See if this step needs to wait for Slack user input.
   * @return Boolean Returns TRUE if this step waits for a Slack user response, FALSE otherwise.
   *   This can be either text input or a button action.
   */
  public function waitForInput () {
    return in_array($this->type, static::$waits_for_input);
  }

}