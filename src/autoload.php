<?php

// Define any constants.
define('GAME_DIRECTORY', GAME_SERVER_ROOT . '/src');

// Add in any class requirements. Not using namespaces, so this has to be done manually.
require_once(GAME_DIRECTORY . '/ActionInterface.php');
require_once(GAME_DIRECTORY . '/DispatcherInterface.php');
require_once(GAME_DIRECTORY . '/Entity.php');
require_once(GAME_DIRECTORY . '/ActionUtils.php');
require_once(GAME_DIRECTORY . '/Attachment.php');
require_once(GAME_DIRECTORY . '/AttachmentButton.php');
require_once(GAME_DIRECTORY . '/AttachmentButtonConfirm.php');
require_once(GAME_DIRECTORY . '/AttachmentField.php');
require_once(GAME_DIRECTORY . '/Channel.php');
require_once(GAME_DIRECTORY . '/HTMLDispatcher.php');
require_once(GAME_DIRECTORY . '/Message.php');
require_once(GAME_DIRECTORY . '/Trigger.php');
require_once(GAME_DIRECTORY . '/Utils.php');
add_requires(GAME_DIRECTORY . '/Action');
add_requires(GAME_DIRECTORY . '/Attachment');
add_requires(GAME_DIRECTORY . '/Slack');
add_requires(GAME_DIRECTORY . '/Core');
// require_once(GAME_DIRECTORY . '/SlackAttachment.php');
// require_once(GAME_DIRECTORY . '/SlackMessage.php');
// require_once(GAME_DIRECTORY . '/ServerUtils.php');
// require_once(GAME_DIRECTORY . '/Core/RPGEntity.php');
// require_once(GAME_DIRECTORY . '/Core/RPGEntitySaveable.php');
// add_requires(GAME_DIRECTORY . '/Core');
// add_requires(GAME_DIRECTORY . '/Core/Entity');
require_once(GAME_DIRECTORY . '/Session.php');

// Loop through the specified directory and require any files inside that do not start with "__" (two underscores).
function add_requires ($dir, $ignore_autoload = true) {
  chdir($dir);

  foreach (glob("[!__]*.php") as $filename) {
    if ($ignore_autoload && $filename == 'autoload.php') continue;
    require_once($filename);
  }

  $dir_count = explode('/', $dir);
  $dir_count = count($dir_count);
  $up_level = '';

  for( $i = 0; $i < $dir_count; $i++) {
    $up_level .= '../';
  }

  chdir($up_level);
}