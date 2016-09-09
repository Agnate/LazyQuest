<?php

namespace Agnate\LazyQuest;

class Guild extends Entity {

  public $gid;
  public $slack_id;
  public $name;
  public $icon;
  public $team_id;

  // Static vars
  static $db_table = 'guilds';
  static $default_class = '\Agnate\LazyQuest\Guild';
  static $partials = array('name');
  static $primary_key = 'gid';
  static $relationships = array(
    'team_id' => '\Agnate\LazyQuest\Team',
  );

  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);
  }

  /**
   * Render the name and icon of the Guild.
   */
  public function display ($bold = TRUE, $display_icon = TRUE) {
    return ($display_icon ? $this->icon.' ' : '') . ($bold ? '*' : '') . $this->name . ($bold ? '*' : '');
  }

  /**
   * Return the Slack channel name for this player.
   */
  public function getChannelName () {
    return '@' . $name;
  }

}