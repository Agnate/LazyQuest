<?php

use Agnate\RPG\Guild;

namespace Agnate\RPG\Message;

class Channel {

  public $channel_type;
  public $guilds;

  const TYPE_DIRECT = 'direct';
  const TYPE_PUBLIC = 'public';

  static $types = array(Channel::TYPE_PUBLIC, Channel::TYPE_DIRECT);

  /**
   * Get the channel information necessary for a message.
   * @param $channel_type Type of Channel (from static::$types — currently Channel::TYPE_PUBLIC, Channel::TYPE_DIRECT).
   * @param $guilds Array of Guilds this message goes to. If the Channel type is Channel::TYPE_DIRECT, message will be sent to each Guild directly.
   */
  function __construct($channel_type, $guilds = NULL) {
    if (!in_array($channel_type, static::$types)) throw new \Exception ('Channel type must one of the types specified in Channel, ' . $channel_type . ' given.');
    if (!empty($guilds) && !is_array($guilds) && !($guilds instanceof \Agnate\RPG\Guild)) throw new \Exception ('Channel guilds must be NULL, an Array of Guilds, or an individual Guild, ' . $guilds . ' given.');

    // If this is one Guild, put in an array.
    if (!empty($guilds) && $guilds instanceof \Agnate\RPG\Guild) {
      $guilds = array($guilds);
    }

    $this->channel_type = $channel_type;
    $this->guilds = $guilds;

    // Instantiate guilds if it's not an array.
    if (empty($this->guilds) && !is_array($this->guilds)) {
      $this->guilds = array();
    }
  }
}