<?php

// This the file that Slack buttons send responses to.
require_once('../bootstrap.php');

// Data should come from $_POST['payload'].
if (empty($_POST['payload'])) exit;
$payload = json_decode($_POST['payload'], TRUE);

/*
See: https://api.slack.com/docs/message-buttons#overview

Steps:
- Validate that payload is real (use SLACK_OAUTH_VERIFICATION config to compare to "token")
- Identify user/Guild who sent the message.
- Retrieve the current command in queue (this likely needs to be stored in
    database - you can use the "callback_id" that's sent in the original message,
    which you can grab below).
- Process the button they clicked (the "actions value").
- Update the current command in queue with new results (if any).
- Send back a payload update to refresh the buttons however necessary.
*/

// Construct the ServerResponder so we can use the chat.update feature of bots.
$team = \Agnate\RPG\Team::load(array('team_id' => $payload['team']['id']));
$responder = new \Agnate\RPG\ServerResponder ($team);

// Get the constructed message.
// We will need to add some additional fields:
//  ts -> This is the timestamp of the original message, which we need. Use $payload['message_ts'] as the value.
//  attachments_clear -> Set this to TRUE to clear out any attachments that might be there on original message when it gets updated.
//  channel -> This must always be Channel::TYPE_REPLY and we use the $payload['channel']['id'] as the value.
$message = new \Agnate\RPG\Message (array(
  'channel' => new \Agnate\RPG\Message\Channel (\Agnate\RPG\Message\Channel::TYPE_REPLY, NULL, $payload['channel']['id']),
  'text' => "Woo! Action chosen: " . $payload['actions'][0]['name'],
  'ts' => $payload['message_ts'],
  'attachments_clear' => TRUE,
));

$responder->update($message);


/*{
  "actions": [
    {
      "name": "recommend",
      "value": "yes"
    }
  ],
  "callback_id": "comic_1234_xyz",
  "team": {
    "id": "T47563693",
    "domain": "watermelonsugar"
  },
  "channel": {
    "id": "C065W1189",
    "name": "forgotten-works"
  },
  "user": {
    "id": "U045VRZFT",
    "name": "brautigan"
  },
  "action_ts": "1458170917.164398",
  "message_ts": "1458170866.000004",
  "attachment_id": "1",
  "token": "xAB3yVzGS4BQ3O9FACTa8Ho4",
  "original_message": "{\"text\":\"New comic book alert!\",\"attachments\":[{\"title\":\"The Further Adventures of Slackbot\",\"fields\":[{\"title\":\"Volume\",\"value\":\"1\",\"short\":true},{\"title\":\"Issue\",\"value\":\"3\",\"short\":true}],\"author_name\":\"Stanford S. Strickland\",\"author_icon\":\"https://api.slack.com/img/api/homepage_custom_integrations-2x.png\",\"image_url\":\"http://i.imgur.com/OJkaVOI.jpg?1\"},{\"title\":\"Synopsis\",\"text\":\"After @episod pushed exciting changes to a devious new branch back in Issue 1, Slackbot notifies @don about an unexpected deploy...\"},{\"fallback\":\"Would you recommend it to customers?\",\"title\":\"Would you recommend it to customers?\",\"callback_id\":\"comic_1234_xyz\",\"color\":\"#3AA3E3\",\"attachment_type\":\"default\",\"actions\":[{\"name\":\"recommend\",\"text\":\"Recommend\",\"type\":\"button\",\"value\":\"recommend\"},{\"name\":\"no\",\"text\":\"No\",\"type\":\"button\",\"value\":\"bad\"}]}]}",
  "response_url": "https://hooks.slack.com/actions/T47563693/6204672533/x7ZLaiVMoECAW50Gw1ZYAXEM"
}*/