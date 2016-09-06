<?php
// Add these to composer's requires if they aren't already there:
// "devristo/phpws": "dev-master"
// "frlnc/php-slack": "*"

require_once('bootstrap.php');

// Include game source.
use Agnate\RPG\Session;

// Create API call to start websocket connection.
use Frlnc\Slack\Http\SlackResponseFactory;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Core\Commander;


/* =====================================================
   _________    __  _________   __    ____  ____  ____ 
  / ____/   |  /  |/  / ____/  / /   / __ \/ __ \/ __ \
 / / __/ /| | / /|_/ / __/    / /   / / / / / / / /_/ /
/ /_/ / ___ |/ /  / / /___   / /___/ /_/ / /_/ / ____/ 
\____/_/  |_/_/  /_/_____/  /_____/\____/\____/_/      
                                                                                                                 
======================================================== */

$interactor = new CurlInteractor;
$interactor->setResponseFactory(new SlackResponseFactory);

// Need to set up a Commander for each team.
$teams = Agnate\RPG\Team::loadMultiple();

$commander = new Commander(SLACK_OAUTH_CLIENT_SECRET, $interactor);

$response = $commander->execute('chat.postMessage', array(
  'channel' => SLACK_BOT_PUBLIC_CHANNEL,
  'username' => SLACK_BOT_USERNAME,
  'as_user' => TRUE,
  'text' => 'Hello, world!',
));

var_export($response);

// Start the RPM session.
/*$response = $commander->execute('rtm.start', array());
$body = $response->getBody();
// Check for an okay response and get url.
if (isset($body['ok']) && $body['ok']) $url = $body['url'];
else {
  echo "Failed to initiate the rtm.start call:\n";
  echo var_export($body, true)."\n";
  exit;
}

// Create list of IMs.
$im_channels = gather_im_channels($commander);

// Create list of Users.
$user_list = gather_user_list($commander);

//echo var_export($im_channels, true)."\n";
//echo var_export($response->toArray(), true)."\n";


// Create websocket connection.
$loop = \React\EventLoop\Factory::create();




// Add any timers necessary.
$loop->addPeriodicTimer(2, 'timer_process_queue');
$loop->addPeriodicTimer(31, 'timer_reset_tavern');
$loop->addPeriodicTimer(32, 'timer_trickle_tavern');
$loop->addPeriodicTimer(33, 'timer_refresh_quests');
$loop->addPeriodicTimer(34, 'timer_leaderboard_standings');


$logger = new \Zend\Log\Logger();
$writer = new \Zend\Log\Writer\Stream("php://output");
$logger->addWriter($writer);

$client = new \Devristo\Phpws\Client\WebSocket ($url, $loop, $logger);

$client->on("request", function($headers) use ($logger) {
  $logger->notice("Request object created.");
});

$client->on("handshake", function() use ($logger) {
  $logger->notice("Handshake received.");
});

$client->on("connect", function() use ($logger, $client) {
  $logger->notice("Connected.");
});

$client->on("message", function($message) use ($client, $logger) {
  // Only keep track of messages and reactions.
  $data = json_decode($message->getData(), true);

  // $logger->notice($data);

  // If a new IM channel is opened, refresh the list.
  if (isset($data['type']) && $data['type'] == 'im_created') {
    global $im_channels, $commander;
    $im_channels = gather_im_channels($commander);
    return;
  }

  // If a new team member joins, refresh the list.
  else if (isset($data['type']) && $data['type'] == 'team_join') {
    global $im_channels, $commander;
    $user_list = gather_user_list($commander);
    return;
  }

  // If a user changes their username, update their Guild.
  else if (isset($data['type']) && $data['type'] == 'user_change') {
    update_user($data);
    return;
  }
  
  // Reaction (aka confirmation) from the user.
  else if (isset($data['type']) && $data['type'] == 'reaction_added' && isset($data['reaction']) && $data['reaction'] == 'confirm') {
    // Skip if we don't have the appropriate data.
    if (!isset($data['user'])) return;
    if (!isset($data['item'])) return;

    // Get the message and make sure it's from RPG bot in a personal message.
    $item = $data['item'];
    if (!isset($item['ts'])) return;
    if (!isset($item['type']) || $item['type'] != 'message') return;
    if (!isset($item['channel'])) return;

    global $im_channels, $user_list, $commander;
    $user_id = $data['user'];
    $channel = $item['channel'];

    // Get the list of current reactions to find the message (very tediuos step).
    $response = $commander->execute('reactions.list', array('user' => $user_id, 'count' => 5, 'page' => 1));
    $body = $response->getBody();
    $reaction = null;
    if (isset($body['ok']) && $body['ok']) {
      foreach ($body['items'] as $areaction) {
        if ($areaction['channel'] != $channel) continue;
        if ($areaction['message']['type'] != 'message') continue;
        if ($areaction['message']['ts'] != $item['ts']) continue;
        $reaction = $areaction;
        break;
      }
    }
    if (empty($reaction)) return;

    // Check that the user data exists.
    if (!isset($user_list[$user_id])) return;
    $user = $user_list[$user_id];

    // $logger->notice("Got reaction from user: ".$message->getData());

    // Get the message text.
    $orig_text = $reaction['message']['text'];

    // Look for confirmation snippet.
    preg_match("/Type `\+:confirm:` to confirm\.\\n\(You typed: `(.+)`\)/", $orig_text, $matches);
    if (count($matches) < 2) return;
    $text = $matches[1].' CONFIRM';

    // $logger->notice("Reaction command: ".$text);
  }

  // Message from the user.
  else if (isset($data['type']) && $data['type'] == 'message' && !isset($data['subtype'])) {
    // Skip if we don't have the appropriate data.
    if (!isset($data['user'])) return;
    if (!isset($data['channel'])) return;

    global $im_channels, $user_list;
    $user_id = $data['user'];
    $channel = $data['channel'];

    // Get the personal message channel.
    if (!isset($im_channels[$user_id])) return;
    $im_channel = $im_channels[$user_id];

    // Check that it is a personal message channel.
    if ($channel != $im_channel) return;

    // Check that the user data exists.
    if (!isset($user_list[$user_id])) return;
    $user = $user_list[$user_id];

    // $logger->notice("Got personal message from user: ".$message->getData());

    // Get the message text.
    $text = $data['text'];
  }

  // If we have some text, process it.
  if (isset($text) && !empty($text)) {
    // Bust it up and send it as a command to RPGSession.
    $session_data = array(
      'user_id' => $user_id,
      'user_name' => $user['name'],
    );
    $session = new RPGSession ();
    $response = $session->handle($text, $session_data);
    //$logger->notice($response);

    // Send the messages to the users.
    if (isset($response['personal']) && !empty($response['personal'])) {
      foreach ($response['personal'] as $personal_message) {
        send_message($personal_message);
      }
    }

    // If there's a global message, send that.
    if (isset($response['channel']) && !empty($response['channel'])) {
      send_message($response['channel']);
    }
  }
});

$client->open();
$loop->run();*/