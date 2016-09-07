<?php

use Agnate\RPG\Guild;

namespace Agnate\RPG\Message;

class Channel {

  public $type;
  public $guilds;
  public $channel_id;

  const TYPE_DIRECT = 'direct';
  const TYPE_PUBLIC = 'public';
  const TYPE_REPLY = 'reply';

  static $types = array(Channel::TYPE_PUBLIC, Channel::TYPE_DIRECT, Channel::TYPE_REPLY);

  /**
   * Get the channel information necessary for a message.
   * @param $type Type of Channel (from static::$types — currently Channel::TYPE_PUBLIC, Channel::TYPE_DIRECT).
   * @param $guilds Array of Guilds this message goes to. If the Channel type is Channel::TYPE_DIRECT, message will be sent to each Guild directly.
   * @param $channel_id Set to the channel ID when the type is set to Channel::TYPE_REPLY.
   */
  function __construct($type, $guilds = NULL, $channel_id = '') {
    if (!in_array($type, static::$types)) throw new \Exception ('Channel type must one of the types specified in Channel, ' . $type . ' given.');
    if (!empty($guilds) && !is_array($guilds) && !($guilds instanceof \Agnate\RPG\Guild)) throw new \Exception ('Channel guilds must be NULL, an Array of Guilds, or an individual Guild, ' . $guilds . ' given.');
    if ($type == Channel::TYPE_REPLY && empty($channel_id)) throw new \Exception ('Channel ID must be provided when Channel::TYPE_REPLY is chosen.');

    // If this is one Guild, put in an array.
    if (!empty($guilds) && $guilds instanceof \Agnate\RPG\Guild) {
      $guilds = array($guilds);
    }

    $this->type = $type;
    $this->guilds = $guilds;
    $this->channel_id = $channel_id;

    // Instantiate guilds if it's not an array.
    if (empty($this->guilds) && !is_array($this->guilds)) {
      $this->guilds = array();
    }
  }
}