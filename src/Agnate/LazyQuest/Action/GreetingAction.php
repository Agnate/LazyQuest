<?php

namespace Agnate\LazyQuest\Action;

use \Agnate\LazyQuest\ActionChain;
use \Agnate\LazyQuest\ActionData;
use \Agnate\LazyQuest\ActionState;
use \Agnate\LazyQuest\EntityBasic;
use \Agnate\LazyQuest\Action\ActionInterface;
use \Agnate\LazyQuest\Message;
use \Agnate\LazyQuest\Message\Channel;
use \Agnate\LazyQuest\Message\Attachment;
use \Agnate\LazyQuest\Message\AttachmentButton;
use \Agnate\LazyQuest\Message\AttachmentButtonConfirm;

class GreetingAction extends EntityBasic implements ActionInterface {

  public static function perform (ActionData $data, $state = NULL) {
    $button_groups = array();

    // If they haven't registered, they need to do that before anything else.
    if (empty($data->guild())) {
      $button_groups[] = array(
        'title' => 'Registration',
        'text' => 'To play Lazy Quest, you must register your guild. This involves choosing a guild name and emoji which will represent you in Lazy Quest.',
        'buttons' => array(
          array('text' => 'Register', 'value' => ActionChain::create(array('hello', 'register'))),
        ),
      );
    }
    // Otherwise, show them all of the options.
    else {
      $button_groups[] = array(
        'title' => 'Questing and exploring',
        'text' => 'Send adventurers into the world',
        'buttons' => array(
          array('text' => 'Map', 'value' => ActionChain::create(array('hello', 'map'))),
          array('text' => 'Explore', 'value' => ActionChain::create(array('hello', 'explore'))),
          array('text' => 'Quest', 'value' => ActionChain::create(array('hello', 'quest'))),
          array('text' => 'Raid', 'value' => ActionChain::create(array('hello', 'raid'))),
        ),
      );

      $button_groups[] = array(
        'title' => 'Inventory and items',
        'text' => 'View and trade consumables and relics',
        'buttons' => array(
          array('text' => 'Inventory', 'value' => ActionChain::create(array('hello', 'inventory'))),
          array('text' => 'Buy', 'value' => ActionChain::create(array('hello', 'buy'))),
          array('text' => 'Sell', 'value' => ActionChain::create(array('hello', 'sell'))),
          array('text' => 'Give', 'value' => ActionChain::create(array('hello', 'give'))),
        ),
      );
      // Per-item actions: use, equip, sell

      $button_groups[] = array(
        'title' => 'Guild',
        'text' => 'Manage your guild and search for other players',
        'buttons' => array(
          array('text' => 'Status', 'value' => ActionChain::create(array('hello', 'status'))),
          array('text' => 'Upgrade', 'value' => ActionChain::create(array('hello', 'upgrade'))),
          array('text' => 'Leaderboard', 'value' => ActionChain::create(array('hello', 'leaderboard'))),
          array('text' => 'Search', 'value' => ActionChain::create(array('hello', 'search'))),
        ),
      );

      $button_groups[] = array(
        'title' => 'Adventurers',
        'text' => 'View, promote, empower, equip, dismiss, and recruit adventurers',
        'buttons' => array(
          array('text' => 'View', 'value' => ActionChain::create(array('hello', 'adventurers'))),
          array('text' => 'Recruit', 'value' => ActionChain::create(array('hello', 'recruit'))),
        ),
      );
      // Per-adventurer actions: promote, empower, equip, dismiss

      $button_groups[] = array(
        'title' => 'Colosseum',
        'text' => 'See and challenge other guilds in the Colosseum for fame and fortune',
        'buttons' => array(
          array('text' => 'Requests', 'value' => ActionChain::create(array('hello', 'requests'))),
          array('text' => 'Challenge', 'value' => ActionChain::create(array('hello', 'challenge'))),
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
        $chain = $button['value']->encoded();
        $attachment->addButton(new AttachmentButton (array(
          'text' => $button['text'],
          'value' => $chain,
          'name' => !empty($button['name']) ? $button['name'] : $chain,
        )));
      }

      $attachments[] = $attachment;
    }

    $message = Message::reply('Hello there! What would you like to do?', $data->channel, $data, FALSE);
    $message->attachments = $attachments;
    return $message;
  }

}