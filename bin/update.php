<?php

/**
 * This script updates the database to the specified game version.
 * Example of updating to version 0.2.0:
 * 
 * (from  /slckslsh/dev  directory)
 *   php bin/update.php -v 0.2.0
 *
 * Options:
 *    -v    Version number to update to.
 *    -f    Force the script to run the update in the -v option, even if it is up to date.
 *
 */

require_once('bootstrap.php');

use \Agnate\LazyQuest\Updater;

// Get the parameters passed in from the PHP command line.
$shortopts = 'v:'; // Required
$shortopts .= 'f::'; // Optional
$longopts = array(
  'version:', // Required
  'force::', // Optional
);
$opts = getopt($shortopts, $longopts);

// If no version is sent, we're done.
if (empty($opts['v'])) {
  print "Please add a version number to update to. Example:  php bin/update.php -v 1.2.3\n";
  exit;
}

$update_version = $opts['v'];
$force_update = isset($opts['f']);

// Get the updater so we can compare versions.
$updater = Updater::get();
$updater->perform($update_version, $force_update);