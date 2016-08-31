<?php

use Agnate\RPG\Entity;

namespace Agnate\RPG\Message;

/**
 * Mimics the settings of Slack's attachment actions button.
 */
class AttachmentButton extends \Agnate\RPG\Entity {

  // Slack's attachment options and data go here.
  public $confirm;
  public $name;
  public $style;
  public $text;
  public $type;
  public $value;
  
  const TYPE_BUTTON = 'button';
  const STYLE_DEFAULT = 'default';
  const STYLE_PRIMARY = 'primary';
  const STYLE_DANGER = 'danger';

  static $types = array(AttachmentButton::TYPE_BUTTON);
  static $styles = array(AttachmentButton::STYLE_DEFAULT, AttachmentButton::STYLE_PRIMARY, AttachmentButton::STYLE_DANGER);

  /**
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class. Keys are:
   *  'confirm' — Instance of AttachmentButtonConfirm. Providing this will pop up dialog with your indicated text and choices.
   *  'name' — Provide a string to give this specific action a name.
   *  'style' — One of AttachmentButton static::$styles.
   *  'text' — The user-facing label for the message button representing this action. Cannot contain markup.
   *  'type' — One of AttachmentButton static::$types.
   *  'value' — Provide a string identifying this specific action.
   * @see Attachment
   * @see AttachmentButtonConfirm
   */
  function __construct($data = array()) {
    // Extra validation.
    if (!empty($data['type']) && !in_array($data['type'], static::$types)) throw new \Exception('AttachmentButton $type must be one of the constants listed in the class static::$types, ' . $data['type'] . ' given.');
    if (!empty($data['style']) && !in_array($data['style'], static::$styles)) throw new \Exception('AttachmentButton $styles must be one of the constants listed in the class static::$styles, ' . $data['style'] . ' given.');

    // Assign data to instance properties.
    parent::__construct($data);

    // Set some defaults.
    if (empty($this->type)) $this->type = AttachmentButton::TYPE_BUTTON;
    if (empty($this->style)) $this->style = AttachmentButton::STYLE_DEFAULT;
  }

  /**
   * Render out the HTML version.
   */
  public function render () {
    $response = array();

    if ($this->type == AttachmentButton::TYPE_BUTTON) {
      $uniqueID = uniqid('confirmbtn-');
      $response[] = '<span class="attachment-button">';
      $response[] = '<input type="button"' . (!empty($this->confirm) ? ' is-confirm="true"' : '') . ' name="' . $this->name . '" value="' . $this->text . '" submit-value="' . $this->value . '" ' . (!empty($this->confirm) ? ' confirm-id="' . $uniqueID . '"' : '') . '>';
      if (!empty($this->confirm)) {
        $response[] = $this->confirm->render($uniqueID);
      }
      $response[] = '</span>';
    }

    return implode('', $response);
  }
}