<?php

// Require any basic config and setup we need.
require_once('basics.php');

// Set up the cache instance.
$cache = Agnate\LazyQuest\App::cache();
$cache->cache->flush();

// Sleep for 2 seconds to make sure Memcached clears.
sleep(1);