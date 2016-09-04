<?php

require_once('../../bootstrap.php');

// if (!isset($_REQUEST['token']) || $_REQUEST['token'] != SLACK_TOKEN) exit;
// if (!isset($_REQUEST['command']) || $_REQUEST['command'] != '/rpg') exit;
if (!isset($_REQUEST['text'])) exit;

$command = trim($_REQUEST['text']);
$session = new Agnate\RPG\Session ();
$messages = $session->run($command, $_REQUEST);

// Dispatch all of the received messages.
$dispatcher = new Agnate\RPG\Dispatcher\HTMLDispatcher ();
foreach ($messages as $message) {
  // d($dispatcher->dispatch($message));
  print $dispatcher->dispatch($message) . '<br><br>';
}