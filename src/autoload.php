<?php

// Set up an autoloader to load all classes.
spl_autoload_register(function ($class) {
    // Convert namespace to full file path
    $class = str_replace('\\', '/', $class);
    include($class . '.php');
});