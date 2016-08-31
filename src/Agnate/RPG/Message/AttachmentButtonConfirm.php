<?php

use Agnate\RPG\Entity;

namespace Agnate\RPG\Message;

/**
 * Mimics the settings of Slack's attachment action button confirm settings.
 */
class AttachmentButtonConfirm extends \Agnate\RPG\Entity {

  // Slack's attachment options and data go here.
  public $title;
  public $text;
  public $ok_text;
  public $dismiss_text;

  /**
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class. Keys are:
   *  'title' — Title the pop up window. Please be brief.
   *  'text' — Describe in detail the consequences of the action and contextualize your button text choices.
   *  'ok_text' — The text label for the button to continue with an action. Keep it short. Defaults to 'Okay'.
   *  'dismiss_text' —  The text label for the button to cancel the action. Keep it short. Defaults to 'Cancel'.
   */
  function __construct($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);

    // Set some defaults.
    if (empty($this->ok_text)) $this->ok_text = 'Okay';
    if (empty($this->dismiss_text)) $this->dismiss_text = 'Cancel';
  }

  /**
   * Render out the HTML version.
   */
  public function render ($uniqueid = NULL) {
    $response = array();
    $response[] = '<div class="attachment-button-confirm" ' . (!empty($uniqueid) ? ' id="' . $uniqueid . '"' : '') . '>';
    $response[] = '<span class="alert-title">' . $this->title . '</span><br>';
    $response[] = '<span class="alert-text">' . \Agnate\RPG\Utils::convertMarkup($this->text) . '</span><br>';
    $response[] = '<input class="alert-ok" type="button" value="' . $this->ok_text . '">';
    $response[] = '<input class="alert-dismiss" type="button" value="' . $this->dismiss_text . '">';
    $response[] = '</div>';
    
    return implode('', $response);
  }
}