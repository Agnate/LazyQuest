<?php

namespace Agnate\Tests;

use \Agnate\LazyQuest\Data\TokenData;
use \PHPUnit\Framework\TestCase;

class TokenDataTest extends TestCase {

  /**
   * @dataProvider tokenDataProvider
   */
  public function testGeneral($data) {

    // ---------------------------
    // __construct() & properties
    // ---------------------------
    $token = new TokenData ($data['key'], $data['data']);
    
    // $key
    $this->assertEquals($token->key, $data['key']);
    
    // $join
    if (isset($data['data']['join'])) $this->assertEquals($token->join, $data['data']['join']);
    else $this->assertEquals($token->join, '');

    // $parts
    if (isset($data['data']['parts'])) $this->assertEquals($token->parts, $data['data']['parts']);
    else $this->assertEquals($token->parts, '');



    // -------------------------------
    // TokenData instance functions
    // -------------------------------

    // save()
    // $this->assertTrue($cache->save($data['key'], $data['data']));

    // load()
    // $this->assertEquals($cache->load($data['key']), $data['data']);

  }

  public function tokenDataProvider() {
    // Parameters for each test (using nested Array):
    //    'key' => Data key to store against.
    //    'data' => Data to store in TokenData.

    return [
      'simple token' => [[
        'key' => 'tokendata_test1',
        'data' => array(),
      ]],

      'partial token' => [[
        'key' => 'tokendata_test1',
        'data' => array('parts' => array('test1a')),
      ]],

      'full token' => [[
        'key' => 'tokendata_test2',
        'data' => array('join' => '-', 'parts' => array('test2a', array('test2b1', 'test2b2', 'test2b3'))),
      ]],

    ];
  }
}