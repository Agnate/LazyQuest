<?php

require_once('../../bootstrap.php');


$chain_text = 'action1';
$chain = Agnate\LazyQuest\ActionChain::decode($chain_text);
d($chain_text, $chain);

$chain_text = 'action1__action2';
$chain = Agnate\LazyQuest\ActionChain::decode($chain_text);
d($chain_text, $chain);

$chain_text = 'action1|subaction1';
$chain = Agnate\LazyQuest\ActionChain::decode($chain_text);
d($chain_text, $chain);

$chain_text = 'action1|subaction1|opt1,opt2';
$chain = Agnate\LazyQuest\ActionChain::decode($chain_text);
d($chain_text, $chain);

$chain_text = 'action1|subaction1|opt1,opt2__action2';
$chain = Agnate\LazyQuest\ActionChain::decode($chain_text);
d($chain_text, $chain);

$chain_text = 'action1|subaction1|opt1,opt2__action2|subaction2|opt3,opt4';
$chain = Agnate\LazyQuest\ActionChain::decode($chain_text);
d($chain_text, $chain);

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

// foreach ($messages as $message) {
//   print "<code><pre>" . var_export($message->jsonSerialize(), true) . "</pre></code>";
//   // d(json_encode($message->jsonSerialize()));
//   $url = http_build_query($message->jsonSerialize());
//   d($url);
//   d(urldecode($url));
// }

// $test = array(
//   'text' => 'Hello world',
//   'username' => 'Lazy Quest',
//   'icon_emoji' => ':rpg:',
//   'attachments' => array(
//     array (
//       'title' => 'Attachment Test',
//       'text' => 'This is an attachment test. Please click a button:',
//       'attachment_type' => 'default',
//     ),
//   ),
// );
// $url = http_build_query($test);
// d($url);
// d(urldecode($url));