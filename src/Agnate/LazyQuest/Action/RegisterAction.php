<?php

namespace Agnate\LazyQuest\Action;

use \Agnate\LazyQuest\ActionData;
use \Agnate\LazyQuest\ActionState;
use \Agnate\LazyQuest\EntityBasic;
use \Agnate\LazyQuest\Message;
use \Agnate\LazyQuest\Message\Attachment;

class RegisterAction extends EntityBasic implements ActionInterface {

  const STEP_NAME = 'name';
  const STEP_ICON = 'icon';
  const STEP_CREATION = 'creation';

  protected static $steps = array(
    RegisterAction::STEP_NAME,
    RegisterAction::STEP_ICON,
    RegisterAction::STEP_CREATION,
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
        'action' => $data->action()->encoded(),
        'extra' => array('step' => RegisterAction::STEP_NAME),
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
    $data->callback_id = 'FAKE';
    $data->message_ts = $state->timestamp;

    // Get the response for the appropriate step.
    switch ($state->extra['step']) {
      // Request Guild name.
      case RegisterAction::STEP_NAME:
        $response = static::performName($data, $state);
        break;

      // Request Guild icon.
      case RegisterAction::STEP_ICON:
        $response = static::performIcon($data, $state);
        break;

      // Show summary.
      case RegisterAction::STEP_CREATION:
        $response = static::performCreation($data, $state);
        break;
    }

    return $response;
  }

  /**
   * Request the Guild name from the user.
   */
  protected static function performName (ActionData $data, ActionState $state) {
    // If the action chain exists in data, need to ask for name.
    // print 'ActionData: ' . var_export($data, true) . "\n";
    // print 'Action: ' . var_export($data->action(), true) . "\n";
    if (!empty($data->action()) && $data->action()->currentActionName() == 'register') {
      return Message::reply('Please tell me your Guild\'s name.', $data->channel, $data);
    }

    // They have submitted their Guild name.
    $name = $data->text;
    
    // TO DO: Validate the name.
    $state->extra['name'] = $name;
    $state->extra['step'] = static::nextStep($state->extra['step']);
    $state->save();

    // Wipe out the information that would cause a chat.update, as we need a new message made.
    $data->callback_id = NULL;

    // Perform the next step.
    return Message::reply('Please tell me your Guild\'s icon.', $data->channel, $data);
  }

  /**
   * Request the Guild icon from the user.
   */
  protected static function performIcon (ActionData $data, ActionState $state) {
    // They have submitted their Guild icon.
    $icon = $data->text;
    
    // TO DO: Validate the icon.
    $state->extra['icon'] = $icon;
    $state->extra['step'] = static::nextStep($state->extra['step']);
    $state->save();

    // Wipe out the information that would cause a chat.update, as we need a new message made.
    $data->callback_id = NULL;

    // Perform the next step.
    $message = Message::reply("You have chosen:\n" . $state->extra['icon'] . ' ' . $state->extra['name'], $data->channel, $data);
    $message->addAttachment(Attachment::approval($state->action()->encoded(), 'confirm', 'cancel'));
    return $message;
  }

  /**
   * Create the Guild if confirmed.
   */
  protected static function performCreation (ActionData $data, ActionState $state) {
    // Check the ActionData action value to see which option was chosen.
    $messages = array();
    switch ($data->currentAction()) {
      case 'confirm':
        // Create the Guild.
        $guild = new Guild (array(
          'username' => $data->user_info['name'],
          'name' => $state->extra['name'],
          'icon' => $state->extra['icon'],
          'slack_id' => $data->user,
          'team_id' => $data->team,
        ));
        $success = $guild->save();
        if (empty($success)) {
          $messages[] = Message::error('There was an error saving your new Guild.', $data->channel, $data);
        }
        else {
          $messages[] = Message::reply('You just registered ' . $guild->display() . '.', $data->channel, $data);
          $messages[] = Message::globally($guild->display('U') . ' just registered a Guild called ' . $guild->display() . '!');
        }
        break;
    }

    // Delete the ActionState.
    $state->delete();
    
    // All done.
    return $message;
  }

}