<?php

namespace Agnate\LazyQuest;

use \stdClass;

class ActionState extends Entity {

  public $asid;
  public $team_id;
  public $guild_id; // May not exist
  public $slack_id;
  public $timestamp;
  public $channel_id;
  public $original_message;
  public $action; // Contains ActionChain.
  public $step;
  public $extra;

  protected $_action_chain;
  
  // Static vars
  static $db_table = 'action_states';
  static $default_class = '\Agnate\LazyQuest\ActionState';
  static $primary_key = 'asid';
  static $partials = array();
  static $relationships = array(
    'team_id' => '\Agnate\LazyQuest\Team',
    'guild_id' => '\Agnate\LazyQuest\Guild',
  );
  static $fields_serialize = array('extra');
  static $fields_json = array('original_message');
  static $fields_int = array();
  static $fields_array = array();

  /**
   * Construct the entity and set data inside.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class.
   */
  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);
    
    // Convert $this->action to ActionChain.
    $this->actionChain();
  }

  /**
   * Get the ActionChain instance of the action.
   * @return ActionChain Returns the action as an ActionChain.
   */
  public function actionChain () {
    if (empty($this->_action_chain)) {
      $this->_action_chain = $this->convertAction($this->action);
    }

    return $this->_action_chain;
  }

  /**
   * Convert string action into an ActionChain.
   * @param $action String action (typically from database) to convert.
   * @return ActionChain Returns an ActionChain instance of the action.
   */
  public function convertAction ($action) {
    return ActionChain::create($action);
  }

  /**
   * Generate a callback ID based on unique data.
   * @param $suffix String to add to the end of the callback ID.
   * @return String Returns a callbackID unique to the Slack user.
   */
  public function callbackID ($suffix = NULL) {
    return $this->slack_id . '__' . $this->getRelationship('team_id')->team_id . (!empty($suffix) ? '__' . $suffix : '');
  }

  /**
   * Clear attachments from the Slack message with this ActionState's timestamp.
   * Typically this is used when you want to clear out the Cancel button attachment.
   * @return Message Returns a Message to dispatch that will clear out the attachments.
   *    Note that it clears ALL attachments, not just the Cancel button.
   */
  public function clearAttachments () {
    // Create fake ActionData based on the ActionState timestamp.
    $data = new ActionData ([
      'message_ts' => $this->timestamp,
      'user' => $this->slack_id,
      'team' => $this->getRelationship('team_id')->team_id,
    ]);

    // Extract the text from Message.
    // if (is_array($this->original_message)) $text = $this->original_message['text'];
    // else if (!empty($this->original_message)) $text = $this->original_message->text;

    $text = !empty($this->original_message->text) ? $this->original_message->text : 'Message was not saved.';

    // Clear out the original_message now.
    $this->original_message = new stdClass;

    return Message::reply($text, $this->channel_id, $data, FALSE);
  }


  /* =================================
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  ==================================== */

  /**
   * Load the current ActionState for this Slack user. Sorts by the timestamp to get most recent if timestamp is not provided in $data parameter.
   * @param $data An array of exact values that the query will search for.
   * @return ActionState Returns an instance of ActionState if available.
   */
  public static function current ($data) {
    // Fetch the most recent entry if there's no timestamp.
    $special = empty($data['timestamp']) ? "ORDER BY timestamp DESC" : "";
    return ActionState::load($data, FALSE, FALSE, $special);
  }

}