<?php

namespace Agnate\LazyQuest;

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
  public $user_info;

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
  protected $_season;
  protected $_action_chain;

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
    // Load the current Season if available.
    $this->season();
    // Convert the action to an ActionChain if available.
    $this->actionChain();
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
   * Load the current Season instance.
   * @return Season Returns an instance of Season, or FALSE if nothing was loaded.
   */
  public function season () {
    // Load the Season if available.
    if (empty($this->_season) && $this->_season !== FALSE) {
      $this->_season = Season::current();
    }
    return $this->_team;
  }

  /**
   * Segment the action response from button presses.
   * @return ActionChain Returns the list of action strings in order of when they were clicked.
   */
  public function actionChain () {
    if (empty($this->_action_chain) && $current_action = $this->currentAction()) {
      $this->_action_chain = ActionChain::create($current_action);
    }
    return $this->_action_chain;
  }

  /**
   * Get the current Slack action response name. Best to use actionChain() instead.
   * @see ActionData->actionChain()
   */
  public function currentAction () {
    return !empty($this->actions) ? $this->actions[0]['name'] : '';
  }

  /**
   * Generate a callback ID based on unique data.
   * @param $suffix String to add to the end of the callback ID.
   * @return String Returns a callbackID unique to the Slack user.
   */
  public function callbackID ($suffix = NULL) {
    return $this->user . '__' . $this->team . (!empty($suffix) ? '__' . $suffix : '');
  }

  /**
   * Clear ActionData information so that it will send a new message instead of a chat.update.
   * Currently this is done by clearing the $callback_id property.
   */
  public function clearForNewMessage () {
    $this->callback_id = NULL;
  }

}