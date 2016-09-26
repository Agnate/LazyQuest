<?php

namespace Agnate\LazyQuest;

use \Agnate\LazyQuest\Action\GreetingAction;
use \Agnate\LazyQuest\Message\Channel;
use \Kint;

class Session {

  public $data;
  public $state;
  public $triggers = array();

  /**
   * Create a new Session.
   */
  function __construct() {
    // Start the App if it hasn't started already.
    App::start();

    // Register all the triggers.
    $this->triggers['hi'] = new Trigger (
      ['hi', 'hello', 'hey', 'yo', 'sup', 'howdy', 'hai', 'hay', 'greetings', 'greeting', 'allo', 'salut', 'bonjour', 'konnichiwa', 'ni hao'],
      '\Agnate\LazyQuest\Action\GreetingAction'
    );
    $this->triggers['register'] = new Trigger (['register'], '\Agnate\LazyQuest\Action\RegisterAction');
  }

  /**
   * @return Array of Message instances.
   * @param Array $data List of all of the data passed by Slack. Expected:
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

    // Make sure there is a Season running.
    if (empty($this->data->season())) return array(Message::noSeason('', $this->data->channel, $this->data));

    // See if there is an existing ActionState. If so, it means that this
    // response might be related to the ActionState and we need to adjust the
    // trigger text.
    $this->state = ActionState::current(array(
      'team_id' => $this->data->team()->tid,
      'slack_id' => $this->data->user,
    ));

    if (!empty($this->state)) {
      $chain = $this->state->actionChain();
      $action = $chain->currentActionName();

      // Check all of the triggers to see if there are any Actions to run.
      if ($action) {
        foreach ($this->triggers as $trigger_key => $trigger) {
          // If the input triggers a command, run the action associated with the trigger.
          if ($trigger->isTriggered($action)) {
            $response = $trigger->performAction($this->data, $this->state);
            break;
          }
        }
      }

      // If there's no response, check the text against the regular triggers.
      if (empty($response)) {
        // Delete the ActionState, invalidate the old action message, and test it as a regular command.
        $message_clear = Message::reply("This message is now out of date.", $this->data->channel, $this->data);
        $this->state->delete();
      }
    }

    // If we didn't get a response from the ActionState check, proceed with a regular check.
    if (empty($response)) {
      $action = $this->data->text;

      // Check all of the triggers to see if there are any Actions to run.
      foreach ($this->triggers as $trigger_key => $trigger) {
        // If the input triggers a command, run the action associated with the trigger.
        if ($trigger->isTriggered($action)) {
          $response = $trigger->performAction($this->data, $this->state);
          break;
        }
      }
    }

    // If there's no response, return the "no command found" response.
    if (empty($response))
      $response = array(Message::reply('Command was invalid. Please type `help` to see a list of commands.', $this->data->channel));

    // Convert to array to simplify output handling.
    if (!is_array($response)) $response = array($response);
    // Append the cleared message if applicable.
    if (!empty($message_clear)) $response[] = $message_clear;
    // Return the response.
    return $response;
  }

  /**
   * Make an update to an existing Session entry.
   * @param $data Array of all of the data passed by Slack. See: https://api.slack.com/docs/message-buttons#how_to_respond_to_message_button_actions
   */
  public function update(Array $data) {
    // Convert this to an ActionData instance to make it easier to manage.
    $this->data = new ActionData ($data);
    $this->state = ActionState::load(array(
      'team_id' => $this->data->team()->tid,
      'slack_id' => $this->data->user,
      // 'timestamp' => $this->data->message_ts,
    ));

    // If the data is a Cancel button, delete the ActionState.
    if ($this->isCancelAction($this->data, $this->state)) {
      $this->state->delete();
      $this->state = NULL;
    }

    // Make sure there is a Season running.
    if (empty($this->data->season())) return array(Message::noSeason('', $this->data->channel, $this->data));

    // Route the update through triggers to match the next action.
    $chain = $this->data->actionChain();
    $action = $chain->currentActionName();

    // App::logger()->notice('Action: ' . var_export($chain, true));
    // App::logger()->notice('Data: ' . var_export($this->data, true));
    // App::logger()->notice('State: ' . var_export($this->state, true));

    // Check all of the triggers to see if there are any Actions to run.
    if ($action) {
      foreach ($this->triggers as $trigger_key => $trigger) {
        // If the input triggers a command, run the action associated with the trigger.
        if ($trigger->isTriggered($action)) {
          // print 'Triggered: ' . $trigger->action . "\n";
          $response = $trigger->performAction($this->data, $this->state);
          break;
        }
      }
    }

    // If there's no response, return an "invalid button" response.
    if (empty($response)) return array(Message::error('Button action was invalid.', $this->data->channel));

    // Convert to array to simplify output handling.
    if (!is_array($response)) $response = array($response);
    return $response;
  }

  /**
   * Check if the action is a Cancel action.
   * @return Boolean Returns TRUE if action is a Cancel action, FALSE otherwise.
   */
  protected function isCancelAction (ActionData $data, ActionState $state) {
    if (empty($data)) return FALSE;

    // Message cancel buttons will yield this result.
    if ($data->callback_id == $data->callbackID('cancel')) return TRUE;

    if (empty($state)) return FALSE;
    if (empty($data->callback_id)) return FALSE;

    $chain = $data->actionChain();
    if (empty($chain)) return FALSE;

    $action = $chain->currentAction();
    if (empty($action)) return FALSE;

    // BaseAction approval Messages will yield this result.
    if ($data->callback_id == $state->callbackID('approval') && $action->subaction == 'cancel') return TRUE;

    return FALSE;
  }

}