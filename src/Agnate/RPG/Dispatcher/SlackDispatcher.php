<?php

use Agnate\RPG\Dispatcher\DispatcherInterface;

namespace Agnate\RPG\Dispatcher;

class SlackDispatcher implements DispatcherInterface {
  
  public $deliverer;
  public $logger;

  /**
   * Initialize the message dispatcher with the deliverer and the logger.
   */
  function __construct($deliverer, $logger) {
    $this->deliverer = $deliverer;
    $this->logger = $logger;
  }

  /**
   * Dispatch the message to Slack (or our debug tool).
   */
  public function dispatch (\Agnate\RPG\Message $message) {
    // Form the message.
    // $info = 

    $messages = $message->get();

    // Check if we need to alter the channel for personal messages.
    /*if ($message->is_instant_message()) {
      $message->channel = get_user_channel($message->player->slack_user_id);
    }

    $message->as_user = 'true';
    $message->username = SLACK_BOT_USERNAME;
    if (empty($message->channel)) $message->channel = SLACK_BOT_PUBLIC_CHANNEL;

    // Get message as associative array.
    $payload = $message->encode();

    // Manually encode attachments.
    // if (isset($payload['attachments']) && is_array($payload['attachments'])) {
    //   $payload['attachments'] = json_encode($payload['attachments']);
    // }

    // Send the message through the deliverer (to Slack).
    $response = $deliverer->execute('chat.postMessage', $payload);
    $body = $response->getBody();

    // If the message delivered properly, we're done.
    if ($body['ok']) return TRUE;

    // Log the error message.
    $logger->notice($body);
    return FALSE;*/
  }

}