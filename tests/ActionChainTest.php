<?php

namespace Agnate\Tests;

use Agnate\LazyQuest\ActionChain;
use Agnate\LazyQuest\ActionLink;
use PHPUnit\Framework\TestCase;

class ActionChainTest extends TestCase {

  /**
   * @dataProvider actionStringProvider
   */
  public function testGeneral($data) {

    // __construct() & properties
    $chain = new ActionChain (['actions' => $data['actions']]);
    // $action
    $this->assertEquals($data['actions'], $chain->actions);


    // -----------------------------
    // ActionChain static functions
    // -----------------------------

    // decode()
    $this->assertEquals($data['actions'], ActionChain::decode($data['encoded']));

    // create() [using String]
    $this->assertEquals($chain, ActionChain::create($data['encoded']));
    // create() [using Array]
    $this->assertEquals($chain, ActionChain::create($data['actions']));
    // create() [using params as ActionLink instances]
    $this->assertEquals($chain, forward_static_call_array(['Agnate\LazyQuest\ActionChain', 'create'], $data['actions']));


    // -------------------------------
    // ActionChain instance functions
    // -------------------------------

    // encoding()
    // Test encoding against either the valid action string (if provided) or the original action string.
    if (isset($data['decoded']) && is_string($data['decoded'])) $this->assertEquals($data['decoded'], $chain->encode());
    else $this->assertEquals($data['encoded'], $chain->encode());

    // currentAction()
    $current = end($data['actions']);
    $this->assertEquals($current, $chain->currentAction());

    // currentActionName()
    $this->assertEquals($current->action, $chain->currentActionName());

    // prevAction()
    // prevActionName()
    // Can only be tested if there's two or more ActionLink instances in the chain.
    $length = count($data['actions']);
    if ($length > 1) {
      $prev = $data['actions'][$length - 2];
      $this->assertEquals($prev, $chain->prevAction());
      $this->assertEquals($prev->action, $chain->prevActionName());
    }

    /**
     * alterActionLink()
     * @see testAlterActionLink 
     */

  }

  public function actionStringProvider() {
    // Parameters for each test (using nested Array):
    //    'encoded' => Action string
    //    'decoded' => (optional) Valid encoded action string if not the same as [0]
    //    'actions' => Array of ActionLink instances result from decoding
    //    'altered' => ActionLink of altered data.

    return [
      'simple action' => [[
        'encoded' => 'action1',
        'actions' => [
          new ActionLink (['action' => 'action1']),
        ],
      ]],

      'action with subaction' => [[
        'encoded' => 'action1|subaction1',
        'actions' => [
          new ActionLink (['action' => 'action1', 'subaction' => 'subaction1']),
        ],
      ]],

      'two actions' => [[
        'encoded' => 'action1__action2',
        'actions' => [
          new ActionLink (['action' => 'action1']),
          new ActionLink (['action' => 'action2']),
        ],
      ]],

      'two actions + empty end' => [[
        'encoded' => 'action1__action2__',
        'decoded' => 'action1__action2',
        'actions' => [
          new ActionLink (['action' => 'action1']),
          new ActionLink (['action' => 'action2']),
        ],
      ]],

      'full action + simple action' => [[
        'encoded' => 'action1|subaction1|opt1,opt2__action2',
        'actions' => [
          new ActionLink (['action' => 'action1', 'subaction' => 'subaction1', 'options' => ['opt1', 'opt2']]),
          new ActionLink (['action' => 'action2']),
        ],
      ]],
      
      'two full actions' => [[
        'encoded' => 'action1|subaction1|opt1,opt2__action2|subaction2|opt3,opt4',
        'actions' => [
          new ActionLink (['action' => 'action1', 'subaction' => 'subaction1', 'options' => ['opt1', 'opt2']]),
          new ActionLink (['action' => 'action2', 'subaction' => 'subaction2', 'options' => ['opt3', 'opt4']]),
        ],
      ]],
    ];
  }

  /**
   * @dataProvider alterActionLinkProvider
   */
  public function testAlterActionLink($data) {
    // Test that altering from an ActionChain is the same as altering directly on the link.
    $chain = new ActionChain (['actions' => [clone $data['link']]]);
    $change = $data['change'];

    // Test altering the defaulted currentAction().
    $link = clone $data['link'];
    $link->alter($change->subaction, $change->options);
    $chain->alterActionLink($change->subaction, $change->options);
    $this->assertEquals($chain->currentAction(), $link);

    // Test passing the ActionLink manually to alterActionLink().
    $chain = new ActionChain (['actions' => [clone $data['link']]]);
    $chain->alterActionLink($change->subaction, $change->options, $chain->actions[0]);
    $this->assertEquals($chain->actions[0], $link);

    // Test altering with no options sent.
    $link = clone $data['link'];
    $link->alter($change->subaction);
    $chain = new ActionChain (['actions' => [clone $data['link']]]);
    $chain->alterActionLink($change->subaction);
    $this->assertEquals($chain->currentAction(), $link);
  }

  public function alterActionLinkProvider() {
    return [
      'fill empty link' => [[
        'link' => new ActionLink,
        'change' => new ActionLink (['action' => 'action1', 'subaction' => 'subaction1', 'options' => ['opt1', 'opt2']]),
      ]],
      'empty full link' => [[
        'link' => new ActionLink (['action' => 'action1', 'subaction' => 'subaction1', 'options' => ['opt1', 'opt2']]),
        'change' => new ActionLink,
      ]],
      'swap links' => [[
        'link' => new ActionLink (['action' => 'action1', 'subaction' => 'subaction1', 'options' => ['opt1', 'opt2']]),
        'change' => new ActionLink (['action' => 'action2', 'subaction' => 'subaction2', 'options' => ['opt3', 'opt4']]),
      ]],
      'alter chain current link' => [[
        'link' => new ActionLink (['action' => 'action1', 'subaction' => 'subaction1', 'options' => ['opt1', 'opt2']]),
        'change' => new ActionLink (['action' => 'action2', 'subaction' => 'subaction2', 'options' => ['opt3', 'opt4']]),
      ]]
    ];
  }
}