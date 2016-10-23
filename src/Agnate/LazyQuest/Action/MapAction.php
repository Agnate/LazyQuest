<?php

namespace Agnate\LazyQuest\Action;

use Agnate\LazyQuest\ActionChain;
use Agnate\LazyQuest\ActionData;
use Agnate\LazyQuest\ActionLink;
use Agnate\LazyQuest\ActionState;
use Agnate\LazyQuest\App;
use Agnate\LazyQuest\Guild;
use Agnate\LazyQuest\Message;
use Agnate\LazyQuest\Message\Attachment;
use Agnate\LazyQuest\Message\AttachmentButton;

class MapAction extends BaseAction {

  public $name = 'view map';
  
  const STEP_VIEW_MAP = 'view-map';

  /**
   * Construct the entity and set data inside.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class.
   */
  function __construct ($data = array()) {
    $this->steps = [
      new Step ([
        'name' => static::STEP_VIEW_MAP,
        'function' => 'performViewMap',
        'type' => Step::TYPE_PROCESS,
      ]),
    ];

    // Assign data to instance properties.
    parent::__construct($data);
  }

  /**
   * View the map that has been generated.
   */
  protected function performViewMap (Step $step, ActionData $data, ActionState $state) {
    // Create main text.
    $text[] = "View the map:";

    // Create attachment.
    $attachment = new Attachment ([
      'title' => "World map",
      'callback_id' => $state->callbackID(),
    ]);

    // TODO: Attach the generated map.

    // Create Map-related buttons.
    $link_hello = ActionLink::create('hello');
    $attachment->addButton(AttachmentButton::fromChain(new ActionChain (['actions' => [$link_hello, ActionLink::create('explore')]]), "Explore"));
    $attachment->addButton(AttachmentButton::fromChain(new ActionChain (['actions' => [$link_hello, ActionLink::create('quest')]]), "Quest"));
    $attachment->addButton(AttachmentButton::fromChain(new ActionChain (['actions' => [$link_hello, ActionLink::create('excavate')]]), "Excavate"));
    $attachment->addButton(AttachmentButton::fromChain(new ActionChain (['actions' => [$link_hello]]), "Back"));

    // Create the message.
    $message = Message::reply($text, $data->channel, $data, FALSE);
    $message->attachments = array($attachment);

    return $message;
  }

}