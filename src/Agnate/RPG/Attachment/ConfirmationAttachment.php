<?php

class ConfirmationAttachment extends Attachment {

  /**
   * Create a Confirmation Layout instance that requires the player to click a button to confirm whatever is in the dialogue.
   * @param $destination The destination of the message. Must be one of the options in static::$destinations (Layout::CHANNEL, Layout::PERSONAL).
   * @param $body String of body text to display to the player. Will appear above the attachment.
   * @param $title String containing the title of the attachment.
   * @param $fields Array containing Array entries of title/text to display under the title.
   * @param $buttons Array containing buttons for user to press.
   */
  function __construct($destination, $body, $title, $fields, $buttons) {
    parent::__construct($destination, $body, $image);

    if (!is_array($fields)) throw new Exception('Layout fields must be an Array, ' . $fields . ' given.');
    if (!is_array($buttons)) throw new Exception('Layout buttons must be an Array, ' . $buttons . ' given.');

    // Set layout information.
    $this->title = $title;
    $this->fields = $fields;
    $this->buttons = $buttons;
  }

  public function render ($debug = FALSE) {
    // If we're in debug mode (ie. testing from browser), render in HTML.
    if ($debug) {
      $output = array();
      if (!empty($this->body)) $output[] = $this->convert_to_markup($this->body);
      if (!empty($this->image)) $output[] = '<img class="map" src="' . $this->image . '" />';

      // echo '<u>CHANNEL: '. $location . ($player != null ? ' ('.$player->username.')' : '') .'</u><br><br>';
      // if (!empty($text)) echo '<div class="channel-'.$location.'">'.$this->convert_to_markup($text);
      // if (!empty($attachment)) {
      //   $style = !empty($attachment->color) ? ' style="border-color: '.$attachment->color.';"' : '';
      //   echo '<div class="attachment"'.$style.'>';
      //   if (!empty($attachment->pretext)) echo $this->convert_to_markup($attachment->pretext).'<br>';
      //   if (!empty($attachment->title)) echo $this->convert_to_markup($attachment->title).'<br>';
      //   if (!empty($attachment->text)) echo $this->convert_to_markup($attachment->text).'<br>';
      //   if (!empty($attachment->image_url)) echo '<br><img class="map" src="'.$attachment->image_url.'" />';
      //   echo '</div>';
      // }
      // if (!empty($text)) echo '</div><br><br>';

      return implode('<br>', $output);
    }

    $payload = '';

    // Render payload for Slack.
    return $payload;
  }

}