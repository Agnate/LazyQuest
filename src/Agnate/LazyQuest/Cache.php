<?php

namespace Agnate\LazyQuest;

use \Memcache;

class Cache {

  public $server;
  public $port;
  public $cache;

  /**
   * Initialize an instance to the Cache. Currently using Memcache.
   */
  function __construct ($server, $port, $start = TRUE) {
    $this->server = $server;
    $this->port = $port;

    if ($start) $this->start();
  }

  /**
   * Start the Cache server.
   */
  public function start () {
    $this->cache = new Memcache;
    $this->cache->addServer($this->server, $this->port);
  }

  /**
   * Load data from the Cache.
   * @param string $key The key of the data to load.
   */
  public function load ($key) {
    return $this->cache->get($key);
  }

  /**
   * Save data to the Cache.
   * @param string $key The key that the data should be stored against.
   * @param * $data The data to store. Array is most commonly used but it will accept most things.
   * @return boolean Returns TRUE if it was successful, FALSE otherwise.
   */
  public function save ($key, $data) {
    return $this->cache->set($key, $data);
  }

}