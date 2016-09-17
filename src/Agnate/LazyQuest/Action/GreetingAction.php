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
    $link_hello = ActionLink::create('hello');

    // If they haven't registered, they need to do that before anything else.
    if (empty($data->guild())) {
      $button_groups[] = array(
        'title' => 'Registration',
        'text' => 'To play Lazy Quest, you must register your guild. This involves choosing a guild name and emoji which will represent you in Lazy Quest.',
        'buttons' => array(
          array('text' => 'Register', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('register'))),
          )),
        ),
      );
    }
    // Otherwise, show them all of the options.
    else {
      $button_groups[] = array(
        'title' => 'Questing and exploring',
        'text' => 'Send adventurers into the world',
        'buttons' => array(
          array('text' => 'Map', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('map'))),
          )),
          array('text' => 'Explore', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('explore'))),
          )),
          array('text' => 'Quest', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('quest'))),
          )),
          array('text' => 'Raid', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('raid'))),
          )),
        ),
      );

      $button_groups[] = array(
        'title' => 'Inventory and items',
        'text' => 'View and trade consumables and relics',
        'buttons' => array(
          array('text' => 'Inventory', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('inventory'))),
          )),
          array('text' => 'Buy', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('buy'))),
          )),
          array('text' => 'Sell', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('sell'))),
          )),
          array('text' => 'Give', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('give'))),
          )),
        ),
      );
      // Per-item actions: use, equip, sell

      $button_groups[] = array(
        'title' => 'Guild',
        'text' => 'Manage your guild and search for other players',
        'buttons' => array(
          array('text' => 'Status', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('status'))),
          )),
          array('text' => 'Upgrade', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('upgrade'))),
          )),
          array('text' => 'Leaderboard', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('leaderboard'))),
          )),
          array('text' => 'Search', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('search'))),
          )),
        ),
      );

      $button_groups[] = array(
        'title' => 'Adventurers',
        'text' => 'View, promote, empower, equip, dismiss, and recruit adventurers',
        'buttons' => array(
          array('text' => 'View', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('adventurers'))),
          )),
          array('text' => 'Recruit', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('recruit'))),
          )),
        ),
      );
      // Per-adventurer actions: promote, empower, equip, dismiss

      $button_groups[] = array(
        'title' => 'Colosseum',
        'text' => 'See and challenge other guilds in the Colosseum for fame and fortune',
        'buttons' => array(
          array('text' => 'Requests', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('requests'))),
          )),
          array('text' => 'Challenge', 'value' => new ActionChain (
            array('actions' => array($link_hello, ActionLink::create('challenge'))),
          )),
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