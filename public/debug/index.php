<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once('../../vendor/autoload.php');

use \Kint;

// Set up some user profiles.
$profiles = array();
$profiles['Steve'] = array(
  'user_name' => 'Steve',
  'user_id' => 'U999999K',
  'team_id' => 'T025KTDB7',
);

// Set some default parameters to set in the form.
$data = array(
  'token' => '69sAIh56xoL4Z82Hlfg79L04', // Fake token
);

// Check authorization to add in the token and whatnot.
if (isset($_GET['monkey123'])) {
  require_once('../../config.php');
  // $data['token'] = SLACK_TOKEN;
  $data['type'] = 'message';
  $data['debug'] = 'true';
  // $data['team'] = 'T025KTDB7';
  // Set the default user.
  // $data['user_name'] = 'Steve';
  // $data['user'] = $profiles['Steve'];
  // 'team' => 'T025KTDB7'
}

// token=1ZAIYgNxoSTO7ew2ntSYO0U9&user_id=U2147483697&user_name=Steve&forced_debug_mode=true&command=/rpg&text=test


?><!DOCTYPE html>
<html>
  <head>
    <title>Lazy Quest Debugger</title>
    <link rel="stylesheet" href="css/jquery-ui.css">
    <link rel="stylesheet" href="css/debug.css">
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/jquery-ui.js"></script>
    <script src="js/jquery.easy-confirm-dialog.min.js"></script>
    <script src="js/interface.js"></script>
  </head>
  
  <body>
    <div id="output"></div>

    <div id="interface">
      <form id="interface-form" action="interface.php" method="POST">
        <select id="profile" name="profile">
          <option id="profile-new" value="">- New -</option>
          <?php foreach ($profiles as $name => $info): ?>
            <option <?php print (isset($data['user_name']) && isset($profiles[$data['user_name']]) ? 'selected="selected"' : '') ?> value="<?php print $info['user_id']; ?>"><?php print $name; ?></option>
          <?php endforeach; ?>
        </select>
        <input id="text" type="text" name="text" value="hello" />

        <?php foreach ($data as $key => $value): ?>
          <input type="hidden" name="<?php print $key; ?>" value="<?php print $value; ?>" />
        <?php endforeach; ?>

        <input id="submit" type="submit" value="Submit">
      </form>
    </div>

  </body>
</html>