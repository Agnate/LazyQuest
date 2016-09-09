<?php

namespace Agnate\LazyQuest\Action;

use \Agnate\LazyQuest\ActionData;
use \Agnate\LazyQuest\ActionState;

interface ActionInterface {

  public static function perform (ActionData $data, ActionState $state);

}