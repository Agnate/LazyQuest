<?php

use Agnate\RPG\Entity;

namespace Agnate\RPG\Core;

class Guild extends \Agnate\RPG\Entity {

  public $name;

  function __construct ($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);
  }

  public function getChannelName () {
    return '@' . $name;
  }

}