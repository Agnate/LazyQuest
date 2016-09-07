<?php

use Agnate\RPG\Message;
use Agnate\RPG\Message\Channel;
use Agnate\RPG\Dispatcher\DispatcherInterface;

namespace Agnate\RPG\Dispatcher;

class HTMLDispatcher implements DispatcherInterface {

  /**
   * Initialize the HTML message dispatcher.
   */
  function __construct() {
    
  }

  /**
   * Dispatch the message to Slack (or our debug tool).
   */
  public function dispatch (\Agnate\RPG\Message $message) {
    $response = array();
    
    // Clone messages for multiple channels.
    if ($message->channel->type == \Agnate\RPG\Message\Channel::TYPE_PUBLIC) {
      $response[] = $message->render($message->channel->type, \Agnate\RPG\Message\Channel::TYPE_PUBLIC);
    }
    else if ($message->channel == \Agnate\RPG\Message\Channel::TYPE_DIRECT) {
      // Loop through all Guilds and make a message copy.
      foreach ($message->channel->guilds as $guild) {
        $response[] = $message->render($message->channel->type, $guild->getChannelName());
      }
    }
    else if ($message->channel->type == \Agnate\RPG\Message\Channel::TYPE_REPLY) {
      $response[] = $message->render($message->channel->type, $message->channel->channel_id);
    }

    return implode('', $response);
  }

}