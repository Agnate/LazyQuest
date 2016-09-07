<?php

use \Agnate\RPG\Action\ActionData;

namespace Agnate\RPG\Action;

interface ActionInterface {

  public static function perform (ActionData $data);

}