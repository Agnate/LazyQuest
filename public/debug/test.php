<?php

require_once('../../bootstrap.php');

// Create a server instance and start it up.
// $server = new Agnate\RPG\Server;
// $server->start();

$data = array(
  'type' => 'message',
  'channel' => 'D286C33AR',
  'user' => 'U0265JBJW',
  'text' => 'hello',
  'ts' => '1473045021.000013',
  'team' => 'T025KTDB7',
);

$session = new Agnate\RPG\Session;
$messages = $session->run($data);

foreach ($messages as $message) {
  print "<code><pre>" . var_export($message->jsonSerialize(), true) . "</pre></code>";
  // d(json_encode($message->jsonSerialize()));
  $url = http_build_query($message->jsonSerialize());
  d($url);
  d(urldecode($url));
}

$test = array(
  'text' => 'Hello world',
  'username' => 'Lazy Quest',
  'icon_emoji' => ':rpg:',
  'attachments' => array(
    array (
      'title' => 'Attachment Test',
      'text' => 'This is an attachment test. Please click a button:',
      'attachment_type' => 'default',
    ),
  ),
);
$url = http_build_query($test);
d($url);
d(urldecode($url));