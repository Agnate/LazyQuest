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
      'plain action' => ['action1', array(new ActionLink (array('action' => 'action1')))],
      // 'action and subaction' => ['action1|subaction1', array('action' => 'action1', 'subaction' => 'subaction1')],
      // 'all parts' => ['action1|subaction1|opt1,opt2', array('action' => 'action1', 'subaction' => 'subaction1', 'options' => array('opt1', 'opt2'))],
      // 'missing subaction' => ['action1||opt1,opt2', array('action' => 'action1', 'subaction' => '', 'options' => array('opt1', 'opt2'))],
      // 'empty options' => ['action1|subaction1|', array('action' => 'action1', 'subaction' => 'subaction1')],
    ];
  }
}