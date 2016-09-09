<?php

namespace Agnate\RPG;

use \Agnate\RPG\Action\GreetingAction;
use \Agnate\RPG\Message\Channel;
use \Kint;

class Session {

  public $data;
  public $response;
  public $triggers = array();

  /**
   * Create a new Session.
   */
  function __construct() {
    // Start the App if it hasn't started already.
    App::start();

    // Register all the triggers.
    $this->triggers[] = new Trigger (array('hello', 'hi', 'hey', 'yo', 'sup', 'howdy', 'hai', 'hay'), '\Agnate\RPG\Action\GreetingAction');
    $this->triggers[] = new Trigger (array('register'), '\Agnate\RPG\Action\RegisterAction');
  }

  /**
   * @return Array of Message instances.
   * @param $data Array of all of the data passed by Slack. Expected:
   *   array(
   *     'type' => 'message',
   *     'channel' => 'D99999AR',
   *     'user' => 'U999999W',
   *     'text' => 'hello',
   *     'ts' => '1473045021.000013',
   *     'team' => 'T9999999',
   *     'debug' => FALSE,
   *   )
   */
  public function run (Array $data) {
    // Convert this to an ActionData instance to make it easier to manage.
    $this->data = new ActionData ($data);

    // Check all of the triggers to see if there are any Actions to run.
    foreach ($this->triggers as $trigger) {
      // If the input triggers a command, run the action associated with the trigger.
      if ($trigger->isTriggered($this->data->text)) {
        $this->response = $trigger->performAction($this->data);
        break;
      }
    }

    // If there's no response, return the "no command found" response.
    if (empty($this->response)) {
      $this->response = Message::reply('Command was invalid. Please type `help` to see a list of commands.', $this->data->channel);
    }

    // Convert to array to simplify output handling.
    if (!is_array($this->response)) {
      $this->response = array($this->response);
    }

    return $this->response;
  }

  /**
   * Make an update to an existing Session entry.
   * @param $data Array of all of the data passed by Slack. See: https://api.slack.com/docs/message-buttons#how_to_respond_to_message_button_actions
   */
  public function update(Array $data) {
    // Convert this to an ActionData instance to make it easier to manage.
    $this->data = new ActionData ($data);

    // NOTE: We will need to add some additional fields for all Messages that are Channel::TYPE_UPDATE:
    //  'ts' -> This is the timestamp of the original message, which we need. Use $payload['message_ts'] as the value.
    //  'attachments_clear' -> Set this to TRUE to clear out any attachments that might be there on original message when it gets updated.
    //  'channel' -> This must always be Channel::TYPE_UPDATE and we use the $this->data->channel as the value.

    // Route the update through triggers to match the next action.
    $next_action = $this->data->nextAction();

    // print 'Next action: ' . $next_action . "\n";

    // Check all of the triggers to see if there are any Actions to run.
    foreach ($this->triggers as $trigger) {
      // If the input triggers a command, run the action associated with the trigger.
      if ($trigger->isTriggered($next_action)) {
        // print 'Triggered: ' . $trigger->action . "\n";
        $this->response = $trigger->performAction($this->data);
        break;
      }
    }

    // If there's no response, return an "invalid button" response.
    if (empty($this->response)) {
      $this->response = Message::error('Button action was invalid.', $this->data->channel);
    }

    // Convert to array to simplify output handling.
    if (!is_array($this->response)) {
      $this->response = array($this->response);
    }

    return $this->response;
  }

}