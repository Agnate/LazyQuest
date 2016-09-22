<?php

namespace Agnate\LazyQuest\Action;

use Agnate\LazyQuest\ActionData;
use Agnate\LazyQuest\ActionState;
use Agnate\LazyQuest\App;
use Agnate\LazyQuest\Guild;
use Agnate\LazyQuest\Message;
use Agnate\LazyQuest\Message\Attachment;

class RegisterAction extends BaseAction {

  public $name = 'registration process';
  public $steps;

  const STEP_ASK_NAME = 'ask-name';
  const STEP_PROCESS_NAME = 'process-name';
  const STEP_ASK_ICON = 'ask-icon';
  const STEP_PROCESS_ICON = 'process-icon';
  const STEP_APPROVAL = 'approval';
  const STEP_CREATE = 'create';

  /**
   * Construct the entity and set data inside.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class.
   */
  function __construct ($data = array()) {
    $this->steps = [
      new Step ([
        'name' => static::STEP_ASK_NAME,
        'function' => 'performAskName',
        'type' => Step::TYPE_ASK,
      ]),
      new Step ([
        'name' => static::STEP_PROCESS_NAME,
        'function' => 'performProcessName',
        'type' => Step::TYPE_PROCESS,
      ]),
      new Step ([
        'name' => static::STEP_ASK_ICON,
        'function' => 'performAskIcon',
        'type' => Step::TYPE_ASK,
      ]),
      new Step ([
        'name' => static::STEP_PROCESS_ICON,
        'function' => 'performProcessIcon',
        'type' => Step::TYPE_PROCESS,
      ]),
      new Step ([
        'name' => static::STEP_APPROVAL,
        'function' => 'performApproval',
        'type' => Step::TYPE_APPROVAL,
      ]),
      new Step ([
        'name' => static::STEP_CREATE,
        'function' => 'performCreate',
        'type' => Step::TYPE_PROCESS,
      ]),
    ];

    // Assign data to instance properties.
    parent::__construct($data);
  }

  /**
   * Perform this action based on Slack data and optionally an existing ActionState.
   * @param $data ActionData instance containing a message typed by the Slack user.
   * @param $state ActionState instance containing any previous action information.
   * @return Array Returns an array of Message instances to dispatch. 
   */
  public function perform (ActionData $data, $state = NULL) {
    // If they already have a registered Guild, we're done.
    if (!empty($data->guild())) {
      return Message::reply('You have already registered this season as: '. $data->guild()->display(FALSE) .'.', $data->channel, $data);
    }

    // Perform the action steps.
    return parent::perform($data, $state);
  }

  /**
   * Request the Guild name from the user.
   */
  protected function performAskName (ActionData $data, ActionState $state) {
    // Set ActionState to the next step.
    $this->gotoNextStep($data, $state);

    return Message::reply('Please tell me your Guild\'s name.', $data->channel, $data);
  }

  /**
   * Process the Guild name from the user.
   */
  protected function performProcessName (ActionData $data, ActionState $state) {
    // They have submitted their Guild name.
    $name = $data->text;
    
    // TO DO: Validate the name.

    $state->extra['name'] = $name;

    // Set ActionState to the next step.
    $this->gotoNextStep($data, $state);
  }

  /**
   * Request the Guild icon from the user.
   */
  protected function performAskIcon (ActionData $data, ActionState $state) {
    // TODO: Remove buttons from previous message. Use ActionState's timestamp.

    // Set ActionState to the next step.
    $this->gotoNextStep($data, $state);

    // Send as a new chat instead of updating the current one.
    $data->clearForNewMessage();

    return Message::reply('Please tell me your Guild\'s icon.', $data->channel, $data);
  }

  /**
   * Process the Guild icon from the user.
   */
  protected function performProcessIcon (ActionData $data, ActionState $state) {
    // They have submitted their Guild icon.
    $icon = $data->text;
    
    // TO DO: Validate the icon.

    $state->extra['icon'] = $icon;
    
    // Set ActionState to the next step.
    $this->gotoNextStep($data, $state);
  }

  /**
   * Present user with an approval action.
   */
  protected function performApproval (ActionData $data, ActionState $state) {
    // TODO: Remove buttons from previous message. Use ActionState's timestamp.

    // Set ActionState to the next step.
    $this->gotoNextStep($data, $state);

    // Send as a new chat instead of updating the current one.
    $data->clearForNewMessage();

    $text = "You have chosen:\n" . $state->extra['icon'] . ' ' . $state->extra['name'];
    return $this->getApprovalMessage($text, $data, $state);
  }

  /**
   * Create the Guild if confirmed.
   */
  protected function performCreate (ActionData $data, ActionState $state) {
    $messages = array();
    $chain = $data->actionChain();

    // Check the ActionData action value to see which option was chosen.
    if ($chain->currentAction()->subaction == 'confirm') {
      // Create the Guild.
      $guild = new Guild ([
        'username' => $data->user_info['name'],
        'name' => $state->extra['name'],
        'icon' => $state->extra['icon'],
        'slack_id' => $data->user,
        'team_id' => $data->team()->tid,
      ]);
      $success = $guild->save();
      if (empty($success)) {
        $messages[] = Message::error('There was an error saving your new Guild.', $data->channel, $data);
      }
      else {
        $messages[] = Message::reply('You just registered ' . $guild->display() . '.', $data->channel, $data, FALSE);
        $messages[] = Message::globally($guild->display('U') . ' just registered a Guild named ' . $guild->display() . '!');
      }
    }

    return $messages;
  }

}