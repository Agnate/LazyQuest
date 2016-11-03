<?php

namespace Agnate\LazyQuest\Data;

use \Agnate\LazyQuest\EntityBasic;

class RandomData extends EntityBasic {

  public $text;
  public $keywords;
  public $tokens;

  static $fields_array = array('keywords', 'tokens');

}