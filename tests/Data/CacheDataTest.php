<?php

namespace Agnate\Tests;

use \Agnate\LazyQuest\Data\CacheData;
use \PHPUnit\Framework\TestCase;
use \stdClass;

class CacheDataTest extends TestCase {

  /**
   * @dataProvider cacheDataProvider
   */
  public function testGeneral($data) {

    // ---------------------------
    // __construct() & properties
    // ---------------------------
    // Also tests extract().
    $cache = new CacheData ($data['team'], $data['key'], $data['data']);
    
    // $key
    $this->assertEquals($data['key'], $cache->key);

    // $team
    $this->assertEquals($data['team'], $cache->team);
    
    // $raw
    $this->assertEquals($data['data'], $cache->raw);


    // ---------------------------
    // CacheData static functions
    // ---------------------------

    

    // -------------------------------
    // CacheData instance functions
    // -------------------------------

    // extract()
    // Already tested in __construct().

    // compact()
    $compact = $cache->compact();
    if (isset($data['compact'])) $this->assertEquals($data['compact'], $compact);
    else $this->assertEquals($data['data'], $compact);

    // save()
    $this->assertTrue($cache->save());

    // load()
    $compact = $cache->load($data['key'])->compact();
    if (isset($data['compact'])) $this->assertEquals($data['compact'], $compact);
    else $this->assertEquals($data['data'], $compact);

    // Set the original data.
    $original = new CacheData (NULL, $data['key'], $data['data']);
    $original->save();

    // original()
    $this->assertEquals($original->compact(), $cache->original()->compact());

    // refresh()
    $cache->refresh();
    $this->assertEquals($original->compact(), $cache->compact());

  }

  public function cacheDataProvider() {
    // Parameters for each test (using nested Array):
    //    'key' => Data key to store against.
    //    'data' => Data to store in CacheData.
    //    'compact' => (Optional) Expected data from CacheData compact() if different from 'data' above.

    return [
      'array cache' => [[
        'key' => 'cachedata_test1',
        'team' => 'teamtest1',
        'data' => array(),
      ]],

      'stdClass cache' => [[
        'key' => 'cachedata_test2',
        'team' => 'teamtest2',
        'data' => new stdClass,
      ]],

      'string cache' => [[
        'key' => 'cachedata_test3',
        'team' => 'teamtest3',
        'data' => 'cache tester',
      ]],

    ];
  }
}