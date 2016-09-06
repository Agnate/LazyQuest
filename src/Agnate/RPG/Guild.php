<?php

use Agnate\RPG\Entity;

namespace Agnate\RPG;

class Guild extends Entity {

  public $gid;
  public $slack_id;
  public $name;
  public $icon;
  public $team_id;

  // Static vars
  static $db_table = 'guilds';
  static $default_class = '\Agnate\RPG\Guild';
  static $partials = array('name');
  static $primary_key = 'gid';
  static $relationships = array(
    'team_id' => '\Agnate\RPG\Team',
  );

  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);
  }

  /**
   * Return the Slack channel name for this player.
   */
  public function getChannelName () {
    return '@' . $name;
  }

}