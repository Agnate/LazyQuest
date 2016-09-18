<?php

namespace Agnate\Tests;

use Agnate\LazyQuest\ActionLink;
use PHPUnit\Framework\TestCase;

class ActionLinkTest extends TestCase {

  /**
   * @dataProvider actionStringProvider
   */
  public function testConstruct($action_string, $action_array, $valid_action_string = NULL) {
    $link = new ActionLink ($action_array);
    $this->assertEquals($link->action, $action_array['action']);
    $this->assertEquals($link->subaction, $action_array['subaction']);

    // If there are options, test them.
    if (empty($action_array['options'])) $options = array();
    else $options = $action_array['options'];
    $this->assertEquals($link->options, $options);

    // Test creating from ActionLink static function to see if it matches.
    $this->assertEquals(ActionLink::create($action_string), $link);

    // Test encoding against the valid action string (if provided).
    if (is_string($valid_action_string)) $this->assertEquals($link->encode(), $valid_action_string);
    // Otherwise test against the primary action string.
    else $this->assertEquals($link->encode(), $action_string);

    // Decode the action into an Array.
    $this->assertEquals(ActionLink::decode($action_string), $action_array);
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
    ];
  }
}