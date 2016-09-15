<?php

namespace Agnate\LazyQuest;

class ActionState extends Entity {

  public $asid;
  public $team_id;
  public $guild_id; // May not exist
  public $slack_id;
  public $timestamp;
  public $action; // Contains ActionChain.
  public $extra;

  protected $_action_chain;
  
  // Static vars
  static $db_table = 'action_states';
  static $default_class = '\Agnate\LazyQuest\ActionState';
  static $primary_key = 'asid';
  static $partials = array();
  static $relationships = array(
    'team_id' => '\Agnate\LazyQuest\Team',
    'guild_id' => '\Agnate\LazyQuest\Guild',
  );
  static $fields_serialize = array('extra');
  static $fields_int = array();
  static $fields_array = array();

  /**
   * Construct the entity and set data inside.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class.
   */
  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);
    
    // Convert $this->action to ActionChain.
    $this->action();
    // if (!empty($this->action) && is_string($this->action))
    //   $this->action = ActionChain::create($this->action);
    // else if (empty($this->action) || !($this->action instanceof \Agnate\RPG\ActionChain))
    //   $this->action = new ActionChain;
  }

  /**
   * Get the ActionChain instance of the action.
   * @return ActionChain Returns the action as an ActionChain.
   */
  public function action () {
    if (empty($this->_action_chain)) {
      $this->_action_chain = $this->convertAction($this->action);
    }

    return $this->_action_chain;
  }

  /**
   * Convert string action into an ActionChain.
   * @param $action String action (typically from database) to convert.
   * @return ActionChain Returns an ActionChain instance of the action.
   */
  public function convertAction ($action) {
    return ActionChain::create($action);
  }


  /* =================================
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  ==================================== */

  /**
   * Load the current ActionState for this Slack user. Sorts by the timestamp to get most recent if timestamp is not provided in $data parameter.
   * @param $data An array of exact values that the query will search for.
   * @return ActionState Returns an instance of ActionState if available.
   */
  public static function current ($data) {
    // Fetch the most recent entry if there's no timestamp.
    $special = empty($data['timestamp']) ? "ORDER BY timestamp DESC" : "";
    return ActionState::load($data, FALSE, FALSE, $special);
  }

}