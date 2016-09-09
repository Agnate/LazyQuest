<?php

namespace Agnate\RPG\Dispatcher;

use \Agnate\RPG\Message;
use \Agnate\RPG\Message\Channel;

class HTMLDispatcher implements DispatcherInterface {

  /**
   * Initialize the HTML message dispatcher.
   */
  function __construct() {
    
  }

  /**
   * Dispatch the message to Slack (or our debug tool).
   */
  public function dispatch (Message $message) {
    $response = array();
    
    // Clone messages for multiple channels.
    if ($message->channel->type == Channel::TYPE_PUBLIC) {
      $response[] = $message->render($message->channel->type, Channel::TYPE_PUBLIC);
    }
    else if ($message->channel == Channel::TYPE_DIRECT) {
      // Loop through all Guilds and make a message copy.
      foreach ($message->channel->guilds as $guild) {
        $response[] = $message->render($message->channel->type, $guild->getChannelName());
      }
    }
    else if ($message->channel->type == Channel::TYPE_REPLY || $message->channel->type == Channel::TYPE_UPDATE) {
      $response[] = $message->render($message->channel->type, $message->channel->channel_id);
    }

    return implode('', $response);
  }

}