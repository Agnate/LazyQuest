<?php

namespace Agnate\Tests;

use \Agnate\LazyQuest\Cache;
use \Memcache;
use \PHPUnit\Framework\TestCase;
use \stdClass;

class CacheTest extends TestCase {

  /**
   * @dataProvider cacheProvider
   */
  public function testGeneral($data) {

    // ---------------------------
    // __construct() & properties
    // ---------------------------
    $cache = new Cache;
    // $cache
    $this->assertInstanceOf(Memcache::class, $cache->cache);


    // -------------------------------
    // Cache instance functions
    // -------------------------------

    // save()
    $this->assertTrue($cache->save($data['key'], $data['value']));

    // load()
    $this->assertEquals($data['value'], $cache->load($data['key']));

  }

  public function cacheProvider() {
    // Parameters for each test (using nested Array):
    //    'key' => Data key to store against.
    //    'value' => Data to store in cache.

    $obj = new stdClass;
    $obj->data = 'test3';

    return [
      'simple cache' => [[
        'key' => 'cache_test1',
        'value' => 'test1',
      ]],

      'array cache' => [[
        'key' => 'cache_test2',
        'value' => array('test2'),
      ]],

      'object cache' => [[
        'key' => 'cache_test3',
        'value' => $obj,
      ]],

    ];
  }
}