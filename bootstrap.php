<?php

// Enable error reporting.
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Enable UTF-8.
ini_set('default_charset', 'UTF-8');

// Load the necessaries.
require_once('config.php');
require_once('vendor/autoload.php');

// Autoload game code - not needed if using composer's autoload.
// require_once('src/autoload.php');