<?php

use Agnate\RPG\Entity;
use Agnate\RPG\Message\AttachmentButton;
use Agnate\RPG\Message\AttachmentField;

namespace Agnate\RPG\Message;

class Attachment extends \Agnate\RPG\Entity {

  // Slack's attachment options and data go here.
  public $fallback;
  public $color;
  public $pretext;
  public $author_name;
  public $author_link;
  public $author_icon;
  public $title;
  public $title_link;
  public $text;
  public $fields;
  public $image_url;
  public $thumb_url;
  public $footer;
  public $footer_icon;
  public $ts;
  public $attachment_type;
  public $actions;
  public $callback_id;


  const TYPE_DEFAULT = 'default';

  // Set any field keys that are expecting arrays.
  static $fields_array = array('fields', 'actions');

  /**
   * Create an Attachment for use in the Message class.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class. Keys are:
   *  'fallback' — A plain-text summary of the attachment. This text will be used in clients that don't show formatted text and should not contain any markup.
   *  'color' — An optional value that can either be one of 'good', 'warning', 'danger', or any hex color code (eg. '#439FE0').
   *  'pretext' — This is optional text that appears above the message attachment block.
   *  'author_name' — Small text used to display the author's name.
   *  'author_link' — A valid URL that will hyperlink the author_name text mentioned above.
   *  'author_icon' — A valid URL that displays a small 16x16px image to the left of the author_name text.
   *  'title' — The title is displayed as larger, bold text near the top of a message attachment.
   *  'title_link' — The title text will be hyperlinked by setting a valid URL in the title_link parameter.
   *  'text' — This is the main text in a message attachment, and can contain standard message markup. The content will automatically collapse if it contains 700+ characters or 5+ linebreaks.
   *  'fields' — Array of AttachmentField instances.
   *  'image_url' — A valid URL to an image file that will be displayed inside a message attachment. We currently support the following formats: GIF, JPEG, PNG, and BMP.
   *  'thumb_url' — A valid URL to an image file that will be displayed as a thumbnail on the right side of a message attachment. We currently support the following formats: GIF, JPEG, PNG, and BMP.
   *  'footer' — Brief text to help contextualize and identify an attachment. Limited to 300 characters.
   *  'footer_icon' — To render a small icon beside your footer text, provide a publicly accessible URL string in the footer_icon field.
   *  'ts' — Integer value in "epoch time", the attachment footer will display an additional timestamp value.
   *  'attachment_type' — Always set to Attachment::TYPE_DEFAULT.
   *  'actions' — A collection of actions (buttons) to include in the attachment. A maximum of 5 actions may be provided.
   *  'callback_id' — The provided string will act as a unique identifier for the collection of buttons within the attachment. It will be sent back to your message button action URL with each invoked action.
   * @see Message
   */
  function __construct($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);

    // Set defaults here.
    if (empty($this->attachment_type)) $this->attachment_type = Attachment::TYPE_DEFAULT;
  }

  /**
   * Add an AttachmentButton to this Attachment.
   * @param $button AttachmentButton instance to be added to the Attachment.
   */
  public function addButton (AttachmentButton $button) {
    $this->actions[] = $button;
  }

  /**
   * Add an AttachmentField to this Attachment.
   * @param $field AttachmentField instance to be added to the Attachment.
   */
  public function addField (AttachmentField $field) {
    $this->fields[] = $field;
  }

  /**
   * Provided by JsonSerializable interface.
   */
  public function jsonSerialize() {
    // Gets all of the public variables as a keyed array.
    $payload = call_user_func('get_object_vars', $this);
    // Remove the variables we don't want to serialize to Slack.
    unset($payload['fields']);
    unset($payload['actions']);

    // Render all of the fields, if there are any.
    if (!empty($this->fields)) {
      $payload['fields'] = array();
      foreach ($this->fields as $field) {
        $payload['fields'][] = $field->jsonSerialize();
      }
    }

    // Render all of the actions/buttons, if there are any.
    if (!empty($this->actions)) {
      $payload['actions'] = array();
      foreach ($this->actions as $action) {
        $payload['actions'][] = $action->jsonSerialize();
      }
    }

    return $payload;
  }

  /**
   * Render out the HTML version.
   */
  public function render () {
    $response = array();
    if (!empty($this->pretext)) $response[] = '<p>' . \Agnate\RPG\Utils::convertMarkup($this->pretext) . '</p>';
    if (!empty($this->fallback)) $response[] = '<div class="fallback">Fallback: ' . $this->fallback . '</div>';

    $response[] = '<div class="attachment"' . (!empty($this->color) ? ' style="border-color: ' . $this->color . ';"' : '') . '>';
    
    if (!empty($this->thumb_url) && empty($this->image_url)) $response[] = '<img class="thumbnail" src="' . $this->thumb_url . '" />';

    if (!empty($this->author_name)) {
      $response[] = '<p class="author">'
        . (!empty($this->author_icon) ? '<img src="' . $this->$this->author_icon . '" />' : '')
        . (!empty($this->author_link) ? '<a href="' . $this->author_link . '">' : '')
        . $this->author_name
        . (!empty($this->author_link) ? '</a>' : '')
        . '</p>';
    }

    if (!empty($this->title)) {
      $response[] = '<h2>'
        . (!empty($this->title_link) ? '<a href="' . $this->title_link . '">' : '')
        . $this->title
        . (!empty($this->title_link) ? '</a>' : '')
        . '</h2>';
    }

    if (!empty($this->text)) $response[] = '<p>' . \Agnate\RPG\Utils::convertMarkup($this->text) . '</p>';

    if (!empty($this->fields)) {
      $response[] = '<div class="fields">';
      foreach ($this->fields as $field) {
        $response[] = $field->render();
      }
      $response[] = '</div>';
    }

    if (!empty($this->footer) || !empty($this->footer_icon) || !empty($this->ts)) {
      $response[] = '<p class="footer">'
        . (!empty($this->footer_icon) ? '<img src="' . $this->footer_icon . '" />' : '')
        . (!empty($this->footer) ? $this->footer : '')
        . (!empty($this->footer) && !empty($this->ts) ? ' | ' : '')
        . (!empty($this->ts) ? $this->ts : '')
        . '</p>';
    }

    if (!empty($this->actions)) {
      $response[] = '<div class="actions">';
      foreach ($this->actions as $action) {
        $response[] = $action->render();
      }
      $response[] = '</div>';
    }

    if (!empty($this->callback_id)) $response[] = '<p class="callback_id">Callback ID: ' . $this->callback_id . '</p>';

    if (!empty($this->image_url)) $response[] = '<img src="' . $this->image_url . '" />';

    $response[] = '</div>';

    return implode('', $response);
  }

}