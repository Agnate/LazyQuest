<?php

// This the file that Slack buttons send responses to.
require_once('../bootstrap.php');

// Data should come from $_POST['payload'].
if (empty($_POST['payload'])) exit;
$payload = json_decode($_POST['payload'], TRUE);

// Validate the payload's token.
if ($payload['token'] !== SLACK_OAUTH_VERIFICATION) exit;

// Create the ServerConnection and link to the Team.
// Note: We do not start up the Server, we just need it to initialize the connection.
$server = new \Agnate\RPG\Server;
$server->handle($payload['team']['id'], $payload);