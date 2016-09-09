<?php

namespace Agnate\RPG\Action;

use \Agnate\RPG\ActionData;
use \Agnate\RPG\EntityBasic;
use \Agnate\RPG\Action\ActionInterface;
use \Agnate\RPG\Message;
use \Agnate\RPG\Message\Channel;
use \Agnate\RPG\Message\Attachment;
use \Agnate\RPG\Message\AttachmentButton;
use \Agnate\RPG\Message\AttachmentButtonConfirm;

class GreetingAction extends EntityBasic implements ActionInterface {

  public static function perform (ActionData $data) {
    $button_groups = array();

    // If they haven't registered, they need to do that before anything else.
    if (empty($data->guild())) {
      $button_groups[] = array(
        'title' => 'Registration',
        'text' => 'To play Lazy Quest, you must register your guild. This involves choosing a guild name and emoji which will represent you in Lazy Quest.',
        'buttons' => array(
          array('text' => 'Register', 'value' => 'hello_register'),
        ),
      );
    }
    // Otherwise, show them all of the options.
    else {
      $button_groups[] = array(
        'title' => 'Questing and exploring',
        'text' => 'Send adventurers into the world',
        'buttons' => array(
          array('text' => 'Map', 'value' => 'hello_map'),
          array('text' => 'Explore', 'value' => 'hello_explore'),
          array('text' => 'Quest', 'value' => 'hello_quest'),
          array('text' => 'Raid', 'value' => 'hello_raid'),
        ),
      );

      $button_groups[] = array(
        'title' => 'Inventory and items',
        'text' => 'View and trade consumables and relics',
        'buttons' => array(
          array('text' => 'Inventory', 'value' => 'hello_inventory'),
          array('text' => 'Buy', 'value' => 'hello_buy'),
          array('text' => 'Sell', 'value' => 'hello_sell'),
          array('text' => 'Give', 'value' => 'hello_give'),
        ),
      );
      // Per-item actions: use, equip, sell

      $button_groups[] = array(
        'title' => 'Guild',
        'text' => 'Manage your guild and search for other players',
        'buttons' => array(
          array('text' => 'Status', 'value' => 'hello_status'),
          array('text' => 'Upgrade', 'value' => 'hello_upgrade'),
          array('text' => 'Leaderboard', 'value' => 'hello_leaderboard'),
          array('text' => 'Search', 'value' => 'hello_search'),
        ),
      );

      $button_groups[] = array(
        'title' => 'Adventurers',
        'text' => 'View, promote, empower, equip, dismiss, and recruit adventurers',
        'buttons' => array(
          array('text' => 'View', 'value' => 'hello_adventurers'),
          array('text' => 'Recruit', 'value' => 'hello_recruit'),
        ),
      );
      // Per-adventurer actions: promote, empower, equip, dismiss

      $button_groups[] = array(
        'title' => 'Colosseum',
        'text' => 'See and challenge other guilds in the Colosseum for fame and fortune',
        'buttons' => array(
          array('text' => 'Requests', 'value' => 'hello_requests'),
          array('text' => 'Challenge', 'value' => 'hello_challenge'),
        ),
      );
    }

    // Turn button groups into Attachments.
    $attachments = array();
    foreach ($button_groups as $group) {
      $attachment = new Attachment (array(
        'title' => $group['title'],
        'text' => $group['text'],
        'callback_id' => 'hello',
      ));

      foreach ($group['buttons'] as $button) {
        $attachment->addButton(new AttachmentButton (array(
          'text' => $button['text'],
          'value' => $button['value'],
          'name' => !empty($button['name']) ? $button['name'] : $button['value'],
        )));
      }

      $attachments[] = $attachment;
    }

    $message = Message::reply('Hello there! What would you like to do?', $data->channel, $data, FALSE);
    $message->attachments = $attachments;
    return $message;
  }

}