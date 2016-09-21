<?php

namespace Agnate\LazyQuest;

use \Agnate\LazyQuest\Message\Attachment;
use \Agnate\LazyQuest\Message\AttachmentButton;
use \Agnate\LazyQuest\Message\Channel;
use \Exception;

class Message extends EntityBasic {

  public $channel; // Channel the Message should render to.
  public $slack_channel;
  // public $as_user = TRUE;
  public $text;
  public $attachments;
  public $ts;

  public $username = SLACK_BOT_USERNAME;
  public $icon_emoji = SLACK_BOT_ICON;

  // Special actions that are unset.
  public $attachments_clear;

  // For messages with buttons:
  // public $response_type = 'in_channel';
  // public $replace_original; // boolean - ONLY used when using buttons
  // public $delete_original; // boolean - ONLY used when using buttons

  // Set any field keys that are expecting arrays.
  static $fields_array = array('attachments');
  
  /**
   * Create a new Message instance to populate with data so it can be rendered.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class. Keys are:
   *  'channel' — The Channel for the Message.
   *  'guilds' — The Guild(s) this Message should go to.
   */
  function __construct($data = array()) {
    // Extra validation.
    if (!empty($data['channel']) && !($data['channel'] instanceof Channel)) throw new Exception ('Message channel must be a Channel object, ' . $data['channel'] . ' given.');
    
    // Convert single Guild into an array.
    if (!empty($data['guilds']) && !is_array($data['guilds'])) {
      $this->guilds = array($guilds);
    }

    // Assign data to instance properties.
    parent::__construct($data);
  }

  /**
   * Add an attachment to the Message.
   */
  public function addAttachment (Attachment $attachment) {
    $this->attachments[] = $attachment;
  }

  /**
   * Provided by JsonSerializable interface.
   */
  public function jsonSerialize() {
    // Gets all of the public variables as a keyed array.
    $payload = call_user_func('get_object_vars', $this);
    // Remove the variables we don't want to serialize to Slack.
    unset($payload['channel']);
    unset($payload['slack_channel']);
    if (!empty($this->slack_channel)) $payload['channel'] = $this->slack_channel;

    // Convert attachments.
    unset($payload['attachments']);
    if (!empty($this->attachments)) {
      $attachments = array();
      foreach ($this->attachments as $attachment) {
        $attachments[] = $attachment->jsonSerialize();
      }
      // Must json_encode attachments so that http_build_query doesn't muck up the response URL.
      $payload['attachments'] = json_encode($attachments, TRUE);
    }

    // For chat.update, we have to pass an empty attachments list if we want to clear it.
    if ($this->attachments_clear && empty($this->attachments)) {
      $payload['attachments'] = '[]';
    }

    // Clear all of the NULL values.
    foreach ($payload as $key => $value) {
      if ($value === NULL) unset($payload[$key]);
    }

    return $payload;
  }

  /**
   * Render out the HTML version.
   */
  public function render ($channel_type, $channel_name) {
    $response = array();
    $response[] = '<div class="message">';
    $response[] = '<h1 class="' . $channel_type . '" channel-type="' . $channel_type . '">Channel: ' . $channel_name . '</h1>';
    $response[] = '<p>' . App::convertMarkup($this->text) . '</p>';
    $response[] = '<div class="attachments">';

    foreach ($this->attachments as $attachment) {
      $response[] = $attachment->render();
    }

    $response[] = '</div>';
    $response[] = '</div>';

    return implode('', $response);
  }


  /* =================================
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  ==================================== */

  /**
   * Determine if we should use a Reply or Update for the Channel type based on ActionData.
   * @param $action_data Instance of ActionData to extract information from about the Channel type.
   * @return Constant Returns the Channel type to use. Usually will be either Channel::TYPE_REPLY or Channel::TYPE_UPDATE.
   */
  public static function channelType ($action_data) {
    return (!empty($action_data) && !empty($action_data->callback_id) && !empty($action_data->message_ts)) ? Channel::TYPE_UPDATE : Channel::TYPE_REPLY;
  }

  /**
   * Create a new Message instance as a reply to the current user.
   * @param $text String message to send to user.
   * @param $channel_id Slack channel ID to send this message back to.
   * @param $action_data Instance of ActionData which is used to add extra message items if necessary.
   * @param $cancel Whether or not to add a Cancel button to the message.
   * @return Message Returns an instance of Message.
   */
  public static function reply ($text, $channel_id, $action_data = NULL, $cancel = TRUE, $clear_attachments = TRUE) {
    // Determine the channel type.
    $channel_type = static::channelType($action_data);

    // Create the initial message.
    $message = new Message (array(
      'channel' => new Channel ($channel_type, NULL, $channel_id),
      'text' => $text,
    ));

    // Add additional information if this is an Update.
    if ($channel_type == Channel::TYPE_UPDATE) {
      $message->ts = $action_data->message_ts;
      $message->attachments_clear = $clear_attachments;
    }

    // If there was a previous action, add a Cancel button to return there.
    if ($cancel && ($cancel_button = Attachment::cancelButton($action_data))) {
      $message->addAttachment($cancel_button);
    }

    return $message;
  }

  /**
   * Return an error message to player with instructions to contact Paul.
   * @param $prefix_message String message to prepend to the main message.
   * @param $channel_id Slack channel ID to send this message back to.
   * @param $action_data Instance of ActionData which is used to add extra message items if necessary.
   * @return Message Returns an instance of Message.
   */
  public static function error ($prefix_message, $channel_id, $action_data = NULL) {
    $text = $prefix_message . (!empty($prefix_message) ? "\n" : '') . "Please contact help@lazyquest.dinelle.ca to let Paul know. Sorry!";
    return static::reply($text, $channel_id, $action_data, FALSE);
  }

  /**
   * Return a message to player about there being no active Season.
   * @param $prefix_message String message to prepend to the main message.
   * @param $channel_id Slack channel ID to send this message back to.
   * @param $action_data Instance of ActionData which is used to add extra message items if necessary.
   * @return Message Returns an instance of Message.
   */
  public static function noSeason ($prefix_message, $channel_id, $action_data = NULL) {
    $text = $prefix_message . (!empty($prefix_message) ? "\n" : '') . "There is no active season running. Please wait for Paul to start the next season.";
    return static::reply($text, $channel_id, $action_data, FALSE);
  }

  /**
   * Broadcast a message to all public channels registered for a specific team.
   * @param $text The text string to send to the public channels.
   * @return Message Returns an instance of Message.
   */
  public static function globally ($text) {
    return new Message (array(
      'channel' => new Channel (Channel::TYPE_PUBLIC),
      'text' => $text,
    ));
  }

}