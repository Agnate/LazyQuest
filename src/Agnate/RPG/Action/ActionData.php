<?php

use \Agnate\RPG\EntityBasic;

namespace Agnate\RPG\Action;

class ActionData extends \Agnate\RPG\EntityBasic {

  public $type;
  public $channel;
  public $user;
  public $text;
  public $ts;
  public $team;
  public $debug;

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
    if (empty($this->_guild) && !empty($this->session_data['user'])) {
      $this->_guild = \Agnate\RPG\Guild::load(array('slack_id' => $this->session_data['user']));
      if (empty($this->_guild)) unset($this->_guild);
    }
  }

  /**
   * Load the Team instance based on the team ID.
   * @return Team Returns an instance of Team.
   */
  public function team () {
    // Load the Team if available.
    if (empty($this->_team) && !empty($this->session_data['team'])) {
      $this->_team = \Agnate\RPG\Team::load(array('team_id' => $this->session_data['team']));
      if (empty($this->_team)) unset($this->_team);
    }
  }

}