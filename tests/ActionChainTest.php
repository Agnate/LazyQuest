<?php

namespace Agnate\Tests;

use Agnate\LazyQuest\ActionChain;
use Agnate\LazyQuest\ActionLink;
use PHPUnit\Framework\TestCase;

class ActionChainTest extends TestCase {

  /**
   * @dataProvider decodeProvider
   */
  public function testDecode($action_string, $result) {
    $actions = ActionChain::decode($action_string);
    $this->assertEquals($actions, $result);
    // $this->assertContainsOnlyInstancesOf(ActionLink::class, $actions);
  }

  public function decodeProvider() {
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