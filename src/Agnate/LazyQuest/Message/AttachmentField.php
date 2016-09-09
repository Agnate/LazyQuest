<?php

namespace Agnate\LazyQuest\Message;

use \Agnate\LazyQuest\App;
use \Agnate\LazyQuest\EntityBasic;

/**
 * Mimics the settings of Slack's attachment fields.
 */
class AttachmentField extends EntityBasic {

  // Slack's attachment options and data go here.
  public $title;
  public $value;
  public $short;

  /**
   * Create an AttachmentField instance for use in Attachments.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class. Keys are:
   *  'title' — Shown as a bold heading above the value text. It cannot contain markup and will be escaped for you.
   *  'value' — The text value of the field. It may contain standard message markup and must be escaped as normal. May be multi-line.
   *  'short' — (Boolean) An optional flag indicating whether the value is short enough to be displayed side-by-side with other values.
   * @see Attachment
   */
  function __construct($title, $value, $short = FALSE) {
    // Assign data to instance properties.
    parent::__construct($data);
  }

  /**
   * Render out the HTML version.
   */
  public function render () {
    $response = array();
    $response[] = '<div class="attachment-field' . ($this->short ? ' short' : '') . '">';
    $response[] = '<strong>' . $this->title . '</strong><br>';
    $response[] = App::convertMarkup($this->value);
    $response[] = '</p>';
    $response[] = '</div>';
    
    return implode('', $response);
  }
}