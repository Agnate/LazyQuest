<?php

namespace Agnate\LazyQuest\Action;

use Agnate\LazyQuest\ActionChain;
use Agnate\LazyQuest\ActionData;
use Agnate\LazyQuest\ActionLink;
use Agnate\LazyQuest\ActionState;
use Agnate\LazyQuest\EntityBasic;
use Agnate\LazyQuest\Action\ActionInterface;
use Agnate\LazyQuest\Message;
use Agnate\LazyQuest\Message\Channel;
use Agnate\LazyQuest\Message\Attachment;
use Agnate\LazyQuest\Message\AttachmentButton;
use Agnate\LazyQuest\Message\AttachmentButtonConfirm;

class GreetingAction extends EntityBasic implements ActionInterface {

  public static function perform (ActionData $data, $state = NULL) {
    $button_groups = array();
    $link_hello = ActionLink::create('hello');

    // If they haven't registered, they need to do that before anything else.
    if (empty($data->guild())) {
      $button_groups[] = [
        'title' => 'Registration',
        'text' => 'To play Lazy Quest, you must register your guild. This involves choosing a guild name and emoji which will represent you in Lazy Quest.',
        'buttons' => [
          ['text' => 'Register', 'value' => new ActionChain (
            ['actions' => [clone $link_hello, ActionLink::create('register')]]
          )],
        ],
      ];
    }
    // Otherwise, show them all of the options.
    else {
      $button_groups[] = [
        'title' => 'Questing and exploring',
        'text' => 'Send adventurers into the world',
        'buttons' => [
          ['text' => 'Map', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('map')]]
          )],
          ['text' => 'Explore', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('explore')]]
          )],
          ['text' => 'Quest', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('quest')]]
          )],
          ['text' => 'Raid', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('raid')]]
          )],
        ],
      ];

      $button_groups[] = [
        'title' => 'Inventory and items',
        'text' => 'View and trade consumables and relics',
        'buttons' => [
          ['text' => 'Inventory', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('inventory')]]
          )],
          ['text' => 'Buy', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('buy')]]
          )],
          ['text' => 'Sell', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('sell')]]
          )],
          ['text' => 'Give', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('give')]]
          )],
        ],
      ];
      // Per-item actions: use, equip, sell

      $button_groups[] = [
        'title' => 'Guild',
        'text' => 'Manage your guild and search for other players',
        'buttons' => [
          ['text' => 'Status', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('status')]]
          )],
          ['text' => 'Upgrade', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('upgrade')]]
          )],
          ['text' => 'Leaderboard', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('leaderboard')]]
          )],
          ['text' => 'Search', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('search')]]
          )],
        ],
      ];

      $button_groups[] = [
        'title' => 'Adventurers',
        'text' => 'View, promote, empower, equip, dismiss, and recruit adventurers',
        'buttons' => [
          ['text' => 'View', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('adventurers')]]
          )],
          ['text' => 'Recruit', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('recruit')]]
          )],
        ],
      ];
      // Per-adventurer actions: promote, empower, equip, dismiss

      $button_groups[] = [
        'title' => 'Colosseum',
        'text' => 'See and challenge other guilds in the Colosseum for fame and fortune',
        'buttons' => [
          ['text' => 'Requests', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('requests')]]
          )],
          ['text' => 'Challenge', 'value' => new ActionChain (
            ['actions' => [$link_hello, ActionLink::create('challenge')]]
          )],
        ],
      ];
    }

    // Turn button groups into Attachments.
    $attachments = array();
    foreach ($button_groups as $group) {
      $attachment = new Attachment ([
        'title' => $group['title'],
        'text' => $group['text'],
        'callback_id' => 'hello',
      ]);

      foreach ($group['buttons'] as $button) {
        $chain = $button['value']->encode();
        $attachment->addButton(new AttachmentButton ([
          'text' => $button['text'],
          'value' => $chain,
          'name' => !empty($button['name']) ? $button['name'] : $chain,
        ]));
      }

      $attachments[] = $attachment;
    }

    $message = Message::reply('Hello there! What would you like to do?', $data->channel, $data, FALSE);
    $message->attachments = $attachments;
    return $message;
  }

}