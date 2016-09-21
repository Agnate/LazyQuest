<?php

namespace Agnate\LazyQuest\Action;

use \Agnate\LazyQuest\ActionData;
use \Agnate\LazyQuest\ActionState;
use \Agnate\LazyQuest\App;
use \Agnate\LazyQuest\EntityBasic;
use \Agnate\LazyQuest\Guild;
use \Agnate\LazyQuest\Message;
use \Agnate\LazyQuest\Message\Attachment;

class RegisterAction extends EntityBasic implements ActionInterface {

  const STEP_ASK_NAME = 'ask-name';
  const STEP_PROCESS_NAME = 'process-name';
  const STEP_ASK_ICON = 'ask-icon';
  const STEP_PROCESS_ICON = 'process-icon';
  const STEP_APPROVAL = 'approval';
  const STEP_CREATE = 'create';

  protected static $steps = array(
    RegisterAction::STEP_ASK_NAME,
    RegisterAction::STEP_PROCESS_NAME,
    RegisterAction::STEP_ASK_ICON,
    RegisterAction::STEP_PROCESS_ICON,
    RegisterAction::STEP_APPROVAL,
    RegisterAction::STEP_CREATE,
  );

  public static function perform (ActionData $data, $state = NULL) {

    // If they already have a registered Guild, we're done.
    if (!empty($data->guild())) {
      return Message::reply('You have already registered this season as: '. $data->guild()->display(FALSE) .'.', $data->channel, $data);
    }

    // New ActionState should only be created when the registration process first starts.
    if (empty($state)) {
      $state = new ActionState (array(
        'slack_id' => $data->user,
        'team_id' => $data->team()->tid,
        'timestamp' => $data->message_ts,
        'action' => $data->actionChain()->encode(),
        'extra' => array('step' => RegisterAction::STEP_ASK_NAME),
      ));
      $success = $state->save();
      if (empty($success)) {
        return array(Message::error('There was a problem saving registration action state.', $data->channel, $data));
      }
    }

    // Perform the next step.
    $response = static::performStep($data, $state);

    // If there was no response, there's an error.
    if (empty($response)) {
      $response = Message::error('There was a problem with the registration process.', $data->channel, $data);
    }
    
    // Convert to array and send out response.
    if (!is_array($response)) $response = array($response);
    return $response;
  }

  /**
   * Get the next step in the action.
   */
  protected static function nextStep ($current_step) {
    $key = array_search($current_step, static::$steps);
    if ($key !== FALSE && count(static::$steps) > $key + 1) {
      return static::$steps[$key + 1];
    }

    return FALSE;
  }

  protected static function performStep (ActionData $data, ActionState $state) {
    // Alter the ActionData to have all the timestamp information we need to override the old item.
    // $data->callback_id = 'FAKE';
    // $data->message_ts = $state->timestamp;

    // Get the response for the appropriate step.
    switch ($state->extra['step']) {
      // Request Guild name.
      case RegisterAction::STEP_ASK_NAME:
        $response = static::performAskName($data, $state);
        break;

      // Process Guild name.
      case RegisterAction::STEP_PROCESS_NAME:
        static::performProcessName($data, $state);
        // Continue to next switch case.

      // Request Guild icon.
      case RegisterAction::STEP_ASK_ICON:
        $response = static::performAskIcon($data, $state);
        break;

      // Process Guild icon.
      case RegisterAction::STEP_PROCESS_ICON:
        static::performProcessIcon($data, $state);
        // Continue to next switch case.

      // Show summary and confirmation.
      case RegisterAction::STEP_APPROVAL:
        $response = static::performApproval($data, $state);
        break;

      case RegisterAction::STEP_CREATE:
        $response = static::performCreate($data, $state);
        break;
    }

    return $response;
  }

  /**
   * Request the Guild name from the user.
   */
  protected static function performAskName (ActionData $data, ActionState $state) {
    // if (!empty($data->actionChain()) && $data->actionChain()->currentActionName() == 'register')

    // Save that the next step is saving the Guild name.
    $state->extra['step'] = RegisterAction::STEP_PROCESS_NAME;
    $state->save();

    return Message::reply('Please tell me your Guild\'s name.', $data->channel, $data);
  }

  /**
   * Process the Guild name from the user.
   */
  protected static function performProcessName (ActionData $data, ActionState $state) {
    // They have submitted their Guild name.
    $name = $data->text;
    
    // TO DO: Validate the name.
    $state->extra['name'] = $name;
    $state->extra['step'] = static::nextStep($state->extra['step']);
    $state->save();
  }

  /**
   * Request the Guild icon from the user.
   */
  protected static function performAskIcon (ActionData $data, ActionState $state) {
    // TODO: Remove buttons from previous message. Use ActionState's timestamp.

    // Save that the next step is saving the Guild name.
    $state->timestamp = $data->message_ts;
    $state->extra['step'] = RegisterAction::STEP_PROCESS_ICON;
    $state->save();

    // Wipe out the information that would cause a chat.update, as we need a new message made.
    $data->callback_id = NULL;

    return Message::reply('Please tell me your Guild\'s icon.', $data->channel, $data);
  }

  /**
   * Process the Guild icon from the user.
   */
  protected static function performProcessIcon (ActionData $data, ActionState $state) {
    // They have submitted their Guild icon.
    $icon = $data->text;
    
    // TO DO: Validate the icon.
    $state->extra['icon'] = $icon;
    $state->extra['step'] = static::nextStep($state->extra['step']);
    $state->save();
  }

  /**
   * Present user with an approval action.
   */
  protected static function performApproval (ActionData $data, ActionState $state) {
    // TODO: Remove buttons from previous message. Use ActionState's timestamp.

    // Save that the next step is saving the Guild name.
    $state->timestamp = $data->message_ts;
    $state->extra['step'] = static::nextStep($state->extra['step']);
    $state->save();

    // Wipe out the information that would cause a chat.update, as we need a new message made.
    $data->callback_id = NULL;

    // Action chain.
    $confirm = clone ($state->actionChain());
    $confirm->alterActionLink('confirm');

    $cancel = clone ($state->actionChain());
    $cancel->alterActionLink('cancel');

    // Perform the next step.
    $message = Message::reply("You have chosen:\n" . $state->extra['icon'] . ' ' . $state->extra['name'], $data->channel, $data);
    $message->addAttachment(Attachment::approval($state->callbackID(), $confirm->encode(), $cancel->encode()));
    return $message;
  }

  /**
   * Create the Guild if confirmed.
   */
  protected static function performCreate (ActionData $data, ActionState $state) {
    $messages = array();
    $chain = $data->actionChain();

    // Check the ActionData action value to see which option was chosen.
    switch ($chain->currentAction()->subaction) {
      case 'confirm':
        // Create the Guild.
        $guild = new Guild (array(
          'username' => $data->user_info['name'],
          'name' => $state->extra['name'],
          'icon' => $state->extra['icon'],
          'slack_id' => $data->user,
          'team_id' => $data->team()->tid,
        ));
        $success = $guild->save();
        if (empty($success)) {
          $messages[] = Message::error('There was an error saving your new Guild.', $data->channel, $data);
        }
        else {
          $messages[] = Message::reply('You just registered ' . $guild->display() . '.', $data->channel, $data, FALSE);
          $messages[] = Message::globally($guild->display('U') . ' just registered a Guild named ' . $guild->display() . '!');
        }
        break;
    }

    // Delete the ActionState.
    $state->delete();
    
    // All done.
    return $messages;
  }

}