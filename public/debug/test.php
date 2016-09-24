<?php

require_once('../../bootstrap.php');

// use Agnate\LazyQuest\Team;

// $team = new Team (array(
//   'team_id' => 'T9999999',
//   'team_name' => 'Fenix ᕕ( ᐛ )ᕗ',
//   'bot_user_id' => 'U999999',
//   'bot_access_token' => 'xxxxxxxx',
// ));
// $team->save();


// print Agnate\LazyQuest\Action\RegisterAction::nextStep('name');




// Create a server instance and start it up.
// $server = new Agnate\LazyQuest\Server;
// $server->start();

// $data = array(
//   'type' => 'message',
//   'channel' => 'D286C33AR',
//   'user' => 'U0265JBJW',
//   'text' => 'hello',
//   'ts' => '1473045021.000013',
//   'team' => 'T025KTDB7',
// );

// $session = new Agnate\LazyQuest\Session;
// $messages = $session->run($data);

// d($messages);

// foreach ($messages as $message) {
//   print "<code><pre>" . var_export($message->jsonSerialize(), true) . "</pre></code>";
//   // d(json_encode($message->jsonSerialize()));
//   $url = http_build_query($message->jsonSerialize());
//   d($url);
//   d(urldecode($url));
// }