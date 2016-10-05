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
    $cache = new CacheData ($data['key'], $data['data']);
    
    // $key
    $this->assertEquals($data['key'], $cache->key);
    
    // $raw
    $this->assertEquals($data['data'], $cache->raw);


    // ---------------------------
    // CacheData static functions
    // ---------------------------

    // originalKey()
    $this->assertEquals($data['orig_key'], CacheData::originalKey($data['key']));


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
    $original = new CacheData ($data['orig_key'], $data['data']);
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
        'orig_key' => 'cachedata_test1_ORIG',
        'data' => array(),
      ]],

      'stdClass cache' => [[
        'key' => 'cachedata_test2',
        'orig_key' => 'cachedata_test2_ORIG',
        'data' => new stdClass,
      ]],

      'string cache' => [[
        'key' => 'cachedata_test3',
        'orig_key' => 'cachedata_test3_ORIG',
        'data' => 'cache tester',
      ]],

    ];
  }
}