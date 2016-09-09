<?php

namespace Agnate\LazyQuest\Action;

use \Agnate\LazyQuest\ActionData;
use \Agnate\LazyQuest\EntityBasic;
use \Agnate\LazyQuest\Message;

class RegisterAction extends EntityBasic implements ActionInterface {

  public static function perform (ActionData $data, ActionState $state) {

    // If they already have a registered Guild, we're done.
    if (!empty($data->guild())) {
      return Message::reply('You have already registered this season as: '. $data->guild()->display(FALSE) .'.', $data->channel, $data);
    }

    // Save ActionState so we can come back to this point.
    if (empty($state)) {
      $state = new ActionState (array(
        'slack_id' => '',
        'timestamp' => '',
        'action' => '',
        'extra' => array(),
      ));
    }

    // Request Guild name.

    // Request Guild icon.

    // Show summary and confirmation.

    // Generate new Guild.

    // If they are late to the Team's map, start them off with a quest to compensate.

    // Send out a global message to let on the Slack team know there's a new Guild.

    // Temp message to test out process.
    return Message::reply('Test message', $data->channel, $data);
  }

}