<?php

namespace Agnate\RPG\Action;

use \Agnate\RPG\ActionData;

interface ActionInterface {

  public static function perform (ActionData $data);

}