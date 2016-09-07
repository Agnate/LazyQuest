<?php
use \Kint;
use \Agnate\RPG\App;
use \Agnate\RPG\Trigger;
use \Agnate\RPG\Action\ActionData;
use \Agnate\RPG\Action\GreetingAction;

namespace Agnate\RPG;

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
    $this->triggers[] = new Trigger (array('hello'), '\Agnate\RPG\Action\GreetingAction');
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
    $this->data = new Action\ActionData ($data);

    // Check all of the triggers to see if there are any Actions to run.
    foreach ($this->triggers as $trigger) {
      // If the input triggers a command, run the action associated with the trigger.
      if ($trigger->is_triggered($this->data->text)) {
        $this->response = $trigger->perform_action($this->data);
        break;
      }
    }

    // If there's no response, return the "no command found" response.
    if (empty($this->response)) {
      $this->response = new Message (array(
        'channel' => new Message\Channel (Message\Channel::TYPE_REPLY, NULL, $this->data->channel),
        'text' => 'Command was invalid. Please type `help` to see a list of commands.',
      ));
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
    $this->data = new Action\ActionData ($data);

    // NOTE: We will need to add some additional fields for all Messages that are Channel::TYPE_UPDATE:
    //  'ts' -> This is the timestamp of the original message, which we need. Use $payload['message_ts'] as the value.
    //  'attachments_clear' -> Set this to TRUE to clear out any attachments that might be there on original message when it gets updated.
    //  'channel' -> This must always be Channel::TYPE_UPDATE and we use the $this->data->channel as the value.


    // Create temporary message.
    return array(
      new Message (array(
        'channel' => new Message\Channel (Message\Channel::TYPE_UPDATE, NULL, $this->data->channel),
        'text' => "Woo! Action chosen: " . $this->data->actions[0]['name'],
        'ts' => $this->data->message_ts,
        'attachments_clear' => TRUE,
      ))
    );
  }

}