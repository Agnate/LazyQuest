<?php

use \Agnate\RPG\EntityBasic;

namespace Agnate\RPG\Action;

class ActionData extends \Agnate\RPG\EntityBasic {

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

  /**
   * Construct the entity and set data inside.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class.
   * @param $autoload Boolean of whether or not to autoload related data.
   */
  function __construct ($data = array(), $autoload = TRUE) {
    // Assign data to instance properties.
    parent::__construct($data);

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
  }

  /**
   * Load the Guild instance based on the user ID.
   * @return Guild Returns an instance of Guild.
   */
  public function guild () {
    // Load the Guild if available.
    if (empty($this->_guild) && !empty($this->user)) {
      $user_id = is_string($this->user) ? $this->user : $this->user['id'];
      $this->_guild = \Agnate\RPG\Guild::load(array('slack_id' => $user_id));
      if (empty($this->_guild)) unset($this->_guild);
    }
  }

  /**
   * Load the Team instance based on the team ID.
   * @return Team Returns an instance of Team.
   */
  public function team () {
    // Load the Team if available.
    if (empty($this->_team) && !empty($this->team)) {
      $team_id = is_string($this->team) ? $this->team : $this->team['id'];
      $this->_team = \Agnate\RPG\Team::load(array('team_id' => $team_id));
      if (empty($this->_team)) unset($this->_team);
    }
  }

}