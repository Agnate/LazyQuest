<?php

namespace Agnate\Tests;

use Agnate\LazyQuest\ActionChain;
use Agnate\LazyQuest\ActionLink;
use PHPUnit\Framework\TestCase;

class ActionChainTest extends TestCase {

  /**
   * @dataProvider actionStringProvider
   */
  public function testGeneral($action_string, $action_array, $valid_action_string = NULL) {

    // __construct() & properties
    $chain = new ActionChain (array('actions' => $action_array));
    // $action
    $this->assertEquals($chain->actions, $action_array);


    // -----------------------------
    // ActionChain static functions
    // -----------------------------

    // decode()
    $this->assertEquals(ActionChain::decode($action_string), $action_array);

    // create()
    $this->assertEquals(ActionChain::create($action_string), $chain);


    // -------------------------------
    // ActionChain instance functions
    // -------------------------------

    // encoding()
    // Test encoding against either the valid action string (if provided) or the original action string.
    if (is_string($valid_action_string)) $this->assertEquals($chain->encode(), $valid_action_string);
    else $this->assertEquals($chain->encode(), $action_string);

    // currentAction()
    $current = end($action_array);
    $this->assertEquals($chain->currentAction(), $current);

    // currentActionName()
    $this->assertEquals($chain->currentActionName(), $current->action);

    // prevAction()
    // prevActionName()
    // Can only be tested if there's two or more ActionLink instances in the chain.
    $length = count($action_array);
    if ($length > 1) {
      $prev = $action_array[$length - 2];
      $this->assertEquals($chain->prevAction(), $prev);
      $this->assertEquals($chain->prevActionName(), $prev->action);
    }

  }

  public function actionStringProvider() {
    // Parameters for each test:
    //    [0] Action string
    //    [1] Array result from decoding
    //    [2] (optional) Valid encoded action string if not the same as [0]
    return [
      'simple action' => ['action1', array(
        new ActionLink (array('action' => 'action1')),
      )],

      'action with subaction' => ['action1|subaction1', array(
        new ActionLink (array('action' => 'action1', 'subaction' => 'subaction1')),
      )],

      'two actions' => ['action1__action2', array(
        new ActionLink (array('action' => 'action1')),
        new ActionLink (array('action' => 'action2')),
      )],

      'two actions + empty end' => ['action1__action2__', array(
        new ActionLink (array('action' => 'action1')),
        new ActionLink (array('action' => 'action2')),
      ), 'action1__action2'],

      'full action + simple action' => ['action1|subaction1|opt1,opt2__action2', array(
        new ActionLink (array('action' => 'action1', 'subaction' => 'subaction1', 'options' => array('opt1', 'opt2'))),
        new ActionLink (array('action' => 'action2')),
      )],
      
      'two full actions' => ['action1|subaction1|opt1,opt2__action2|subaction2|opt3,opt4', array(
        new ActionLink (array('action' => 'action1', 'subaction' => 'subaction1', 'options' => array('opt1', 'opt2'))),
        new ActionLink (array('action' => 'action2', 'subaction' => 'subaction2', 'options' => array('opt3', 'opt4'))),
      )],
    ];
  }
}