<?php

namespace Agnate\RPG;

class ActionData extends EntityBasic {

  // Contains the original array of data sent.
  public $raw;

  // Standard fields from Slack data.
  public $type;
  public $channel;
  public $user;
  public $text;
  public $ts;
  public $team;
  public $debug;

  // Additional variables for chat.update calls.
  public $callback_id;
  public $actions;
  public $action_ts;
  public $message_ts;
  public $attachment_id;
  public $token;
  public $original_message;
  public $response_url;

  // Autoloaded based on above data.
  protected $_guild;
  protected $_team;
  protected $_action_list;

  /**
   * Construct the entity and set data inside.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class.
   * @param $autoload Boolean of whether or not to autoload related data.
   */
  function __construct ($data = array(), $autoload = TRUE) {
    // Assign data to instance properties.
    parent::__construct($data);

    // Set the raw data.
    $this->raw = $data;

    // Convert Arrays into a string.
    if (is_array($this->channel)) $this->channel = $this->channel['id'];
    if (is_array($this->team)) $this->team = $this->team['id'];
    if (is_array($this->user)) $this->user = $this->user['id'];

    // Autoload extra data.
    if ($autoload) $this->autoload();
  }

  /**
   * Autoload data based on the session data.
   */
  public function autoload () {
    // Load the Guild if available.
    $this->guild();
    // Load the Team if available.
    $this->team();
    // Segment the action if available.
    $this->actionList();
  }

  /**
   * Load the Guild instance based on the user ID.
   * @return Guild Returns an instance of Guild, or FALSE if nothing was loaded.
   */
  public function guild () {
    // Load the Guild if available.
    if (empty($this->_guild) && !empty($this->user)) {
      $user_id = is_string($this->user) ? $this->user : $this->user['id'];
      $this->_guild = Guild::load(array('slack_id' => $user_id));
    }
    return $this->_guild;
  }

  /**
   * Load the Team instance based on the team ID.
   * @return Team Returns an instance of Team, or FALSE if nothing was loaded.
   */
  public function team () {
    // Load the Team if available.
    if (empty($this->_team) && !empty($this->team)) {
      $team_id = is_string($this->team) ? $this->team : $this->team['id'];
      $this->_team = Team::load(array('team_id' => $team_id));
    }
    return $this->_team;
  }

  /**
   * Segment the action response from button presses.
   * @return Array Returns the list of action strings in order of when they were clicked.
   */
  public function actionList () {
    if ($current_action = $this->currentAction()) {
      $this->_action_list = explode('_', $current_action);
    }
    return $this->_action_list;
  }

  /**
   * Get the current action response name.
   */
  public function currentAction () {
    return !empty($this->actions) ? $this->actions[0]['name'] : '';
  }

  /**
   * Get the previous action. Typically used to load the ActionState.
   * @return String Returns the name of the action previously taken.
   */
  public function prevAction () {
    $action_list = $this->actionList();
    return count($action_list) > 1 ? implode('_', array_slice($action_list, 0, -1)) : '';
  }

  /**
   * Get the next action that will require a response. Typically used to choose the fire a Trigger.
   */
  public function nextAction () {
    $action_list = $this->actionList();
    return count($action_list) > 0 ? end($action_list) : '';
  }

}