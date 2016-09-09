<?php

namespace Agnate\LazyQuest\Dispatcher;

use \Agnate\LazyQuest\Message;

interface DispatcherInterface {

  public function dispatch (Message $message);

}