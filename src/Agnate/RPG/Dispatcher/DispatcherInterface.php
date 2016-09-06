<?php

use Agnate\RPG\Message;

namespace Agnate\RPG\Dispatcher;

interface DispatcherInterface {

  public function dispatch (\Agnate\RPG\Message $message);

}