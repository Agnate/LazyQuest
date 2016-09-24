<?php

namespace Agnate\LazyQuest;

class Guild extends Entity {

  public $gid;
  public $slack_id;
  public $username;
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
   * @param $pattern The pattern to display. Default pattern is "I *N*".
   *    Accepts any Slack markup and the following tokens:
   *    U - Username or Slack handle (example: @paul)
   *    N - Guild name
   *    I - Guild icon
   */
  public function display ($pattern = "I *N*") {
    $tokens = array(
      '|%U%|' => 'U',
      '|%N%|' => 'N',
      '|%I%|' => 'I',
    );
    // Replace single-letter tokens with more complex tokens.
    $tokened = str_replace(array_values($tokens), array_keys($tokens), $pattern);

    // Replace old single-letter tokens with actual values.
    foreach ($tokens as $key => $value) {
      switch ($value) {
        case 'U': $tokens[$key] = '@' . $this->username; break;
        case 'N': $tokens[$key] = $this->name; break;
        case 'I': $tokens[$key] = $this->icon; break;
      }
    }

    // Replace tokens with actual values.
    return str_replace(array_keys($tokens), array_values($tokens), $tokened);
  }

  /**
   * Return the Slack channel name for this player.
   */
  public function getChannelName () {
    return '@' . $name;
  }


  /* =================================
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  ==================================== */

  /**
   * Determine if this is a valid name or not.
   * @param string $name The name (typically sent by the Slack user) to be validated.
   * @return Boolean Returns TRUE if the name is valid, FALSE otherwise.
   */
  public static function validName ($name) {
    return (strlen($name) < 255);
  }

  /**
   * Determine if this is a valid icon or not.
   * @param string $icon The icon (typically sent by the Slack user) to be validated.
   * @return Boolean Returns TRUE if the icon is valid, FALSE otherwise.
   */
  public static function validIcon ($icon) {
    // Starts and ends with a colon.
    return (strlen($icon) < 255 && strpos($icon, ':') === 0 && strrpos($icon, ':') === strlen($icon) - 1);
  }

}