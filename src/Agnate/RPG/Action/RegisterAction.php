<?php

namespace Agnate\RPG\Action;

use \Agnate\RPG\ActionData;
use \Agnate\RPG\EntityBasic;
use \Agnate\RPG\Message;

class RegisterAction extends EntityBasic implements ActionInterface {

  public static function perform (ActionData $data) {

    // If they already have a registered Guild, we're done.
    if (!empty($data->guild())) {
        return Message::reply('You have already registered this season as: '. $data->guild()->display(FALSE) .'.', $data->channel, $data);
    }

    // Get current season.
    // $season = Season::current();
    // if (empty($season)) {
    //   $this->respond('You must wait for a new Season to begin.');
    //   return FALSE;
    // }

    // Temp message to test out process.
    return Message::reply('Registered!', $data->channel, $data);
  }

}