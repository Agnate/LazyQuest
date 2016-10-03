<?php

namespace Agnate\Tests;

use Agnate\LazyQuest\ActionLink;
use PHPUnit\Framework\TestCase;

class ActionLinkTest extends TestCase {

  /**
   * @dataProvider actionStringProvider
   */
  public function testGeneral($action_string, $action_array, $valid_action_string = NULL) {

    // __construct() & properties
    $link = new ActionLink ($action_array);
    // $action
    $this->assertEquals($action_array['action'], $link->action);
    // $subaction
    $this->assertEquals($action_array['subaction'], $link->subaction);
    // $options
    if (empty($action_array['options'])) $options = array();
    else $options = $action_array['options'];
    $this->assertEquals($options, $link->options);


    // ----------------------------
    // ActionLink static functions
    // ----------------------------

    // decode()
    $this->assertEquals($action_array, ActionLink::decode($action_string));

    // create()
    $this->assertEquals($link, ActionLink::create($action_string));


    // ------------------------------
    // ActionLink instance functions
    // ------------------------------

    // encoding()
    // Test encoding against either the valid action string (if provided) or the original action string.
    if (is_string($valid_action_string)) $this->assertEquals($valid_action_string, $link->encode());
    else $this->assertEquals($action_string, $link->encode());
  }

  public function actionStringProvider() {
    // Parameters for each test:
    //    [0] Action string
    //    [1] Array result from decoding
    //    [2] (optional) Valid encoded action string if not the same as [0]
    return [
      'plain action' => ['action1', array('action' => 'action1', 'subaction' => '')],
      'action and subaction' => ['action1|subaction1', array('action' => 'action1', 'subaction' => 'subaction1')],
      'all parts' => ['action1|subaction1|opt1,opt2', array('action' => 'action1', 'subaction' => 'subaction1', 'options' => array('opt1', 'opt2'))],
      'missing subaction' => ['action1||opt1,opt2', array('action' => 'action1', 'subaction' => '', 'options' => array('opt1', 'opt2'))],
      'empty options' => ['action1|subaction1|', array('action' => 'action1', 'subaction' => 'subaction1'), 'action1|subaction1'],
      'extra separator on end' => ['action1|subaction1|opt1,opt2|', array('action' => 'action1', 'subaction' => 'subaction1', 'options' => array('opt1', 'opt2')), 'action1|subaction1|opt1,opt2'],
    ];
  }
}