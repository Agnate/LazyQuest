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
    // Also tests extract().
    $token = new TokenData ($data['key'], $data['data']);
    
    // $key
    $this->assertEquals($data['key'], $token->key);
    
    // $join
    if (isset($data['data']['join'])) $this->assertEquals($data['data']['join'], $token->join);
    else $this->assertEquals('', $token->join);

    // $parts
    if (isset($data['data']['parts'])) $this->assertEquals($data['data']['parts'], $token->parts);
    else $this->assertEquals(array(), $token->parts);


    // ---------------------------
    // TokenData static functions
    // ---------------------------

    // originalKey()
    $this->assertEquals($data['orig_key'], TokenData::originalKey($data['key']));


    // -------------------------------
    // TokenData instance functions
    // -------------------------------

    // extract()
    // Already tested in __construct().

    // compact()
    $compact = $token->compact();
    if (isset($data['compact'])) $this->assertEquals($data['compact'], $compact);
    else $this->assertEquals($data['data'], $compact);

    // save()
    $this->assertTrue($token->save($data['key'], $data['data']));

    // load()
    $compact = $token->load($data['key'])->compact();
    if (isset($data['compact'])) $this->assertEquals($data['compact'], $compact);
    else $this->assertEquals($data['data'], $compact);

    // random()
    $this->assertContains($token->random(FALSE), $data['random']);

    // Set the original data.
    $original = new TokenData ($data['orig_key'], $data['data']);
    $original->save();

    // original()
    $this->assertEquals($original->compact(), $token->original()->compact());

    // refresh()
    $token->refresh();
    $this->assertEquals($original->compact(), $token->compact());

  }

  public function tokenDataProvider() {
    // Parameters for each test (using nested Array):
    //    'key' => Data key to store against.
    //    'data' => Data to store in TokenData.
    //    'compact' => (Optional) Expected data from TokenData compact() if different from 'data' above.
    //    'random' => List of ALL random outcomes that could happen from the random()
    //                function. Use small test cause in 'data' to make your life easier.

    return [
      'simple token' => [[
        'key' => 'tokendata_test1',
        'orig_key' => 'tokendata_test1_ORIG',
        'data' => array(),
        'compact' => array('join' => '', 'parts' => array()),
        'random' => array(''),
      ]],

      'partial token' => [[
        'key' => 'tokendata_test2',
        'orig_key' => 'tokendata_test2_ORIG',
        'data' => array('parts' => array('a')),
        'compact' => array('join' => '', 'parts' => array('a')),
        'random' => array('a'),
      ]],

      'full token' => [[
        'key' => 'tokendata_test3',
        'orig_key' => 'tokendata_test3_ORIG',
        'data' => array('join' => '-', 'parts' => array('a', array('b1', 'b2'))),
        'random' => array('a-b1', 'a-b2'),
      ]],

    ];
  }
}