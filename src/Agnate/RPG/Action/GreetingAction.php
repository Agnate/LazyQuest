<?php

use Agnate\RPG\Entity;
use Agnate\RPG\Action\ActionInterface;
use Agnate\RPG\Message;
use Agnate\RPG\Message\Attachment;
use Agnate\RPG\Message\AttachmentButton;
use Agnate\RPG\Message\AttachmentButtonConfirm;

namespace Agnate\RPG\Action;

class GreetingAction extends \Agnate\RPG\Entity implements ActionInterface {

  public static function perform ($args) {
    return new \Agnate\RPG\Message (array(
      'channel' => new \Agnate\RPG\Message\Channel (\Agnate\RPG\Message\Channel::TYPE_PUBLIC, NULL),
      'text' => 'Hello world',
      'attachments' => array(
        new \Agnate\RPG\Message\Attachment (array(
          'title' => 'Attachment Test',
          'text' => 'This is an attachment test. Please click a button:',
          'callback_id' => 'button_test_interaction1',
          'actions' => array(
            new \Agnate\RPG\Message\AttachmentButton (array(
              'name' => 'button1',
              'text' => 'Button #1',
              'value' => 'button1',
            )),
            new \Agnate\RPG\Message\AttachmentButton (array(
              'name' => 'button2',
              'text' => 'Button #2',
              'value' => 'button2',
            )),
            new \Agnate\RPG\Message\AttachmentButton (array(
              'name' => 'confirm',
              'text' => 'Confirm',
              'value' => 'confirm',
              'confirm' => new \Agnate\RPG\Message\AttachmentButtonConfirm (array(
                'title' => 'Confirm Title',
                'text' => 'To confirm, click Okay. To cancel, click Cancel.',
              )),
            )),
          ),
        )),
      ),
    ));
  }

}