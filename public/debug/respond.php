<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once '../../config.php';
require_once '../../includes/db.inc';

// if (!isset($_REQUEST['token']) || $_REQUEST['token'] != SLACK_TOKEN) exit;
// if (!isset($_REQUEST['command']) || $_REQUEST['command'] != '/rpg') exit;
if (!isset($_REQUEST['text'])) exit;

$command = trim($_REQUEST['text']);

// Load any vendor items needed.
require_once('../../vendor/autoload.php');

// Set up an autoloader to load all classes.
spl_autoload_register(function ($class) {
    // Convert namespace to full file path
    $class = str_replace('\\', '/', $class);
    include('../../src/' . $class . '.php');
});

// require_once('../../src/autoload.php');

$session = new Agnate\RPG\Session ();
$messages = $session->run($command, $_REQUEST);

// Dispatch all of the received messages.
$dispatcher = new Agnate\RPG\Dispatcher\HTMLDispatcher ();
foreach ($messages as $message) {
  // d($dispatcher->dispatch($message));
  print $dispatcher->dispatch($message) . '<br><br>';
}