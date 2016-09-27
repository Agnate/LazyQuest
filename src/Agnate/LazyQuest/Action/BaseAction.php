<?php

namespace Agnate\LazyQuest\Action;

use Agnate\LazyQuest\ActionData;
use Agnate\LazyQuest\ActionState;
use Agnate\LazyQuest\App;
use Agnate\LazyQuest\EntityBasic;
use Agnate\LazyQuest\Exception\GameException;
use Agnate\LazyQuest\Message;
use Agnate\LazyQuest\Message\Attachment;
use Agnate\LazyQuest\Message\Channel;

class BaseAction extends EntityBasic {

  public $name = 'base action';
  public $steps = [];


  /**
   * Perform this action based on Slack data and optionally an existing ActionState.
   * @param $data ActionData instance containing a message typed by the Slack user.
   * @param $state ActionState instance containing any previous action information.
   * @return Array Returns an array of Message instances to dispatch. 
   */
  public function perform (ActionData $data, $state = NULL) {
    // New ActionState should only be created when the action first starts.
    if (empty($state)) {
      $step = reset($this->steps);
      $state = new ActionState ([
        'slack_id' => $data->user,
        'team_id' => $data->team()->tid,
        'timestamp' => $data->message_ts,
        'channel_id' => $data->channel,
        // 'original_message' => $data->original_message,
        'action' => $data->actionChain()->encode(),
        'step' => $step->name,
        'extra' => array(),
      ]);
      $success = $state->save();
      if (empty($success)) {
        return [Message::error('There was a problem saving registration action state.', $data->channel, $data)];
      }
    }

    // Reconstitute the ActionData with the action for instances where we sent a new Slack message
    // instead of updating the existing one (typically happens when text input is required).
    $data->reconstitute($state);

    // App::logger()->notice('Step: ' . var_export($state->step, true));

    // Perform the next step(s).
    $response = $this->performSteps($data, $state);

    // If there was no response, there's an error.
    if (empty($response)) {
      $response = Message::error('There was a problem with the ' . $this->name . '.', $data->channel, $data);
    }
    
    // Convert to array and send out response.
    if (!is_array($response)) $response = array($response);
    return $response;
  }

  /**
   * Perform the series of steps that will lead to the end of the action.
   * @param $data ActionData instance containing a message typed by the Slack user.
   * @param $state ActionState instance containing any previous action information.
   * @return Array Returns an array of Message instances to dispatch.
   */
  public function performSteps (ActionData $data, ActionState $state) {
    // Keep performing steps until you need input or we are out of steps.
    $count = 0;
    $messages = [];
    // Count is used to prevent an infinite loop. If an action has more than 20 steps without iteraction
    // from the user, please refactor the steps.
    while ($count < 20) {
      // Get the current step's instance.
      $step = $this->getStep($state->step);

      // If there's no step, we're at the end, so we're done.
      if (empty($step)) {
        // Need to delete the ActionState since we passed all the steps.
        $state->delete();
        break;
      }

      // If there's no function on this step, the step was incorrectly created.
      if (empty($step->function)) {
        // Need to delete the ActionState since we hit an error.
        $state->delete();
        throw new GameException ('Step "' . $step->name . '" in ' . __CLASS__ . ' did not have an attached function.');
        break;
      }

      // Call the function linked to the step and join the responses with current response.
      $response = call_user_func([$this, $step->function], $step, $data, $state);

      // Figure out how to manage the response.
      if (is_array($response) && !empty($response)) {
        $messages += $response;
      }
      else if ($response instanceof Message) {
        $messages[] = $response;
      }

      // If this step needs to wait for input, we're done.
      if ($step->waitForInput()) break;

      // Check if this step is the last step.
      if ($this->getNextStep($step->name) === FALSE) {
        // Need to delete the ActionState since we passed all the steps.
        $state->delete();
        break;
      }

      // Fail-safe in case something breaks in the loop.
      $count++;
    }

    // Save the reply as the original_message in the ActionState so we can remove Cancel buttons later.
    // foreach ($messages as $message) {
    //   if (!($message->channel instanceof Channel)) continue;
    //   if ($message->channel->type != Channel::TYPE_UPDATE) continue;
    //   $state->original_message = json_decode(json_encode($message));
    //   $state->save();
    // }

    return $messages;
  }

  /**
   * Get the next step in the action.
   * @param $current_step_name The current step the process is on.
   */
  public function getNextStep ($current_step_name) {
    $found_key = FALSE;
    foreach ($this->steps as $key => $step) {
      if ($step->name == $current_step_name) {
        $found_key = TRUE;
        break;
      }
    }
    if (empty($found_key) || !is_int($key)) return FALSE;

    if ($key !== FALSE && count($this->steps) > $key + 1) {
      return $this->steps[$key + 1];
    }

    return FALSE;
  }

  /**
   * Get a step by name.
   * @param $step_name The name of the step to get.
   * @return Step Returns an instance of Step based on the name requested, FALSE if it cannot find one.
   */
  public function getStep ($step_name) {
    if (empty($this->steps)) return FALSE;

    foreach ($this->steps as $step) {
      if ($step->name == $step_name) return $step;
    }

    return FALSE;
  }

  /**
   * Advance the ActionState to the next step.
   * @param $data ActionData instance containing a message typed by the Slack user.
   * @param $state ActionState instance of advance to the next step.
   * @param $save Boolean of whether or not to automatically save the AcionState after advancing.
   * @return Step Returns the next Step instance. Will return FALSE if there is no next step.
   */
  public function gotoNextStep (ActionData $data, ActionState $state, $save = TRUE) {
    // Get the next step.
    $step = $this->getNextStep($state->step);

    // Save to the ActionState.
    $state->step = !empty($step) ? $step->name : '';
    // $state->timestamp = $data->message_ts;
    if ($save) $state->save();

    return $step;
  }

  /**
   * Get a standard approval Message instance.
   * @param string|Array $text The text string to show in the message.
   * @param ActionData $data ActionData instance containing a message typed by the Slack user.
   * @param ActionState $state ActionState instance containing any previous action information.
   * @return Message Returns the Message instance with appropriate attachment(s).
   */
  public function getApprovalMessage ($text, ActionData $data, ActionState $state) {
    // Action chain.
    $confirm = clone ($state->actionChain());
    $confirm->alterActionLink('confirm');

    // Cancel will go back to the previous step.
    $cancel = $state->actionChain()->goBack();
    $cancel->alterActionLink('cancel');

    // Perform the next step.
    $message = Message::reply($text, $data->channel, $data, FALSE);
    $message->addAttachment(Attachment::approval($state->callbackID('approval'), $confirm->encode(), $cancel->encode()));
    return $message;
  }

  /**
   * Adjust the ActionData and ActionState to allow for a new Message instead of updating the original one.
   * This should be used before requesting text input from the user to continue the action properly and
   * remove the previous Cancel button (and all attachments).
   */
  public function newMessage (ActionData $data, ActionState $state) {
    // * @return Message Returns a Message to dispatch to clean up the previous message.

    // Send as a new chat instead of updating the current one.
    $data->clearForNewMessage();

    // TODO: Instead of clearing ALL attachments, we could keep the previous Message serialized in the database and use that
    // to reconstruct the old Message but without any buttons.


    // Reply with no attachments to clear out Cancel buttons.
    // return $state->clearAttachments();
  }
}