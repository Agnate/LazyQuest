<?php

namespace Agnate\RPG\Dispatcher;

use \Agnate\RPG\Message;

interface DispatcherInterface {

  public function dispatch (Message $message);

}