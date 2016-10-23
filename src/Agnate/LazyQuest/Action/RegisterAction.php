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

class RegisterAction extends BaseAction {

  public $name = 'registration process';
  public $steps;
  public $example_guild;

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

    $this->example_guild = new Guild (array(
      'name' => 'Death\'s Rattle',
      'icon' => ':skull:',
    ));

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
  protected function performAskName (Step $step, ActionData $data, ActionState $state) {
    // Set ActionState to the next step.
    $this->gotoNextStep($data, $state);

    $text[] = "Registering a Guild requires two things:";
    $text[] = "- *Guild name:* This is the name of your Guild (example: `" . $this->example_guild->display('N') . "`).";
    $text[] = "- *Guild icon:* This is the emoji icon of your Guild (example: `" . $this->example_guild->display('I') . "`).";
    $text[] = "";
    $text[] = "Your Guild's name will be shown to other players as the icon and name chosen.";
    $text[] = "Example: " . $this->example_guild->display();
    $text[] = "";
    $text[] = "1) *Guild name:*";
    $text[] = "So, what's your Guild's name?";

    return Message::reply($text, $data->channel, $data);
  }

  /**
   * Process the Guild name from the user.
   */
  protected function performProcessName (Step $step, ActionData $data, ActionState $state) {
    // They have submitted their Guild name.
    $name = $data->text;
    
    // Validate the name.
    if (!Guild::validName($name)) {
      // Force the step to repeat since this had an error.
      $step->wait = TRUE;

      // Give an error message back to the user.
      $text[] = "The name you choose must be 255 characters or less. Please choose a shorter name.";
      return Message::reply($text, $data->channel, $data);
    }

    $state->extra['name'] = $name;

    // Set ActionState to the next step.
    $this->gotoNextStep($data, $state);
  }

  /**
   * Request the Guild icon from the user.
   */
  protected function performAskIcon (Step $step, ActionData $data, ActionState $state) {
    // TODO: Remove buttons from previous message. Use ActionState's timestamp.

    // Set ActionState to the next step.
    $this->gotoNextStep($data, $state);

    // Send as a new chat instead of updating the current one.
    $this->newMessage($data, $state);

    // Create the Message.
    $text[] = "2) *Guild icon:*";
    $text[] = "What will your Guild's icon be? Make sure you use an emoji!";
    $messages[] = Message::reply($text, $data->channel, $data);

    return $messages;
  }

  /**
   * Process the Guild icon from the user.
   */
  protected function performProcessIcon (Step $step, ActionData $data, ActionState $state) {
    // They have submitted their Guild icon.
    $icon = $data->text;
    
    // Validate the icon.
    if (!Guild::validIcon($icon)) {
      // Force the step to repeat since this had an error.
      $step->wait = TRUE;

      // Give an error message back to the user.
      $text[] = "The icon you use must be an emoji (example: `" . $this->example_guild->display('I') . "`).";
      return Message::reply($text, $data->channel, $data);
    }

    $state->extra['icon'] = $icon;
    
    // Set ActionState to the next step.
    $this->gotoNextStep($data, $state);
  }

  /**
   * Present user with an approval action.
   */
  protected function performApproval (Step $step, ActionData $data, ActionState $state) {
    // TODO: Remove buttons from previous message. Use ActionState's timestamp.

    // Set ActionState to the next step.
    $this->gotoNextStep($data, $state);

    // Send as a new chat instead of updating the current one.
    $this->newMessage($data, $state);

    // Create a test Guild so that we can just use the display() method to output the Guild name.
    $test_guild = new Guild (array('name' => $state->extra['name'], 'icon' => $state->extra['icon']));

    // Construct the message.
    $text[] = "You have chosen:";
    $text[] = $test_guild->display();
    $text[] = "";
    $text[] = "Are you happy with this name? You will _not_ be able to change your Guild's name for the duration of the game season. You can change your Guild's icon at any time though.";
    $messages[] = $this->getApprovalMessage($text, $data, $state);

    return $messages;
  }

  /**
   * Create the Guild if confirmed.
   */
  protected function performCreate (Step $step, ActionData $data, ActionState $state) {
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
        // Ask if they want to do the tutorial or not.
        $text[] = "You just registered " . $guild->display() . ". Welcome to Lazy Quest!";
        
        $attachment = new Attachment ([
          'title' => "Getting started",
          'text' => "If you have not played Lazy Quest before, click on Tutorial and I can guide you on how to get started, the objectives of the game, and what to expect. If you've played Lazy Quest before, use _Skip_ to take you to the list of actions.",
          'callback_id' => $state->callbackID(),
        ]);

        // Create ActionChains for Tutorial and Skip buttons.
        $link_hello = ActionLink::create('hello');
        $attachment->addButton(AttachmentButton::fromChain(new ActionChain (['actions' => [$link_hello, ActionLink::create('help')]]), "Tutorial", AttachmentButton::STYLE_PRIMARY));
        $attachment->addButton(AttachmentButton::fromChain(new ActionChain (['actions' => [$link_hello]]), "Skip"));

        // Create the message.
        $message = Message::reply($text, $data->channel, $data, FALSE);
        $message->attachments = array($attachment);

        $messages[] = $message;
        $messages[] = Message::globally($guild->display('U') . ' just registered a Guild named ' . $guild->display() . '!');
      }
    }

    return $messages;
  }

}