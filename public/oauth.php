<?php

// Expect:
// ?code=2189931381.74714615431.7955e5228c&state=

/*
Received:

{
  "ok":true,
  "access_token":"xoxp-2189931381-2209623642-74710042480-cd78ad1231",
  "scope":"identify,bot",
  "user_id":"U0265JBJW",
  "team_name":"Fenix \u1555( \u141b )\u1557",
  "team_id":"T025KTDB7",
  "bot":{
    "bot_user_id":"U26M5TNNB",
    "bot_access_token":"xoxb-74719940759-X81hVwidB8jghnlhbHAbQLhA"
  }
}

*/

/*

{
    "access_token": "xoxp-XXXXXXXX-XXXXXXXX-XXXXX",
    "scope": "incoming-webhook,commands,bot",
    "team_name": "Team Installing Your Hook",
    "team_id": "XXXXXXXXXX",
    "incoming_webhook": {
        "url": "https://hooks.slack.com/TXXXXX/BXXXXX/XXXXXXXXXX",
        "channel": "#channel-it-will-post-to",
        "configuration_url": "https://teamname.slack.com/services/BXXXXX"
    },
    "bot":{
        "bot_user_id":"UTTTTTTTTTTR",
        "bot_access_token":"xoxb-XXXXXXXXXXXX-TTTTTTTTTTTTTT"
    }
}

*/

// No code? 404 error.
if (empty($_GET['code'])) {
  http_response_code(404);
  exit;
}

require_once('../bootstrap.php');

// See documenation here: https://api.slack.com/docs/oauth
$params = array(
  'client_id' => SLACK_OAUTH_CLIENT_ID,
  'client_secret' => SLACK_OAUTH_CLIENT_SECRET,
  'code' => $_GET['code'],
);
// Convert params into GET query to append onto oAuth URL.
$query = http_build_query($params);

// GET to Slack's oAuth URL.
$ch = curl_init(SLACK_OAUTH_URL . '?' . $query);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Get the response.
$response = curl_exec($ch);
$json = json_decode($response);

// Close the curl connection.
curl_close($ch);

// Check for a bad response.
if (empty($response) || !property_exists($json, 'ok') || !$json->ok) {
  // Redirect to an error page of some kind.
  print "RESPONSE ERROR:<br>";
  print "<pre><code>" . $response . "</code></pre>";
  exit;
} 

// Check if a Team already exists and if not, create one.
$team = Agnate\RPG\Team::load(array('team_id' => $json->team_id));
if (empty($team)) {
  $team = new Agnate\RPG\Team (array(
    'team_id' => $json->team_id,
    'team_name' => $json->team_name,
    'bot_user_id' => $json->bot->bot_user_id,
    'bot_access_token' => $json->bot->bot_access_token,
  ));
  $saved = $team->save();
  if (empty($saved)) {
    print "ERROR SAVING NEW TEAM INFORMATION.";
    exit;
  }
}

// Confirm bot token and user ID are still correct, otherwise update it.
$update_team = FALSE;

// Update bot_user_id?
if ($team->bot_user_id != $json->bot->bot_user_id) {
  $team->bot_user_id = $json->bot->bot_user_id;
  $update_team = TRUE;
}

// Update bot_access_token?
if ($team->bot_access_token != $json->bot->bot_access_token) {
  $team->bot_access_token = $json->bot->bot_access_token;
  $update_team = TRUE;
}

// If we need to save the team, do so now that we've updated the info.
if ($update_team) {
  $saved = $team->save();
  if (empty($saved)) {
    print "ERROR UPDATING TEAM INFORMATION.";
    exit;
  }
}

// Successful oAuth and registration, so they will be redirected now.
header('Location: ' . GAME_SERVER_PUBLIC_URL . '?registered');