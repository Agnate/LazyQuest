<?php

use \Agnate\RPG\Team;

namespace Agnate\RPG;

class ServerResponder {

  public $interactor;
  public $commander;
  public $updater;
  public $team;

  function __construct(Team $team) {
    $this->interactor = new \Frlnc\Slack\Http\CurlInteractor;
    $this->interactor->setResponseFactory(new \Frlnc\Slack\Http\SlackResponseFactory);

    // Create the basics we need for the connection.
    $this->team = $team;
    $this->commander = new \Frlnc\Slack\Core\Commander($this->team->bot_access_token, $this->interactor);
  }

  /**
   * Send a chat.update to Slack.
   */
  public function update (Message $message) {
    if ($message->channel->type != \Agnate\RPG\Message\Channel::TYPE_REPLY) {
      throw new \Exception ("ServerResponder can only send Channel::TYPE_REPLY messages and this Message uses " . $message->channel->type . ".");
    }

    if (empty($message->ts)) throw new \Exception ("Message requires a ts field to properly perform chat.update.");

    // Adjust the channel so it goes to the right spot.
    $message->slack_channel = $message->channel->channel_id;
    $msg = $message->jsonSerialize();

    // Send the chat.update.
    $response = $this->commander->execute('chat.update', $msg);
    $body = $response->getBody();

    // If the message errored, log it.
    if (empty($body['ok'])) throw new \Exception ('ServerResponder failed the chat.update for the following Message: ' . var_export($body, true) . "\n\nMessage: " . var_export($msg, TRUE));

    return TRUE;
  }

}