<?php

require_once('bootstrap.php');

use Agnate\LazyQuest\Image\SpriteSheet;

// print "\n\n";
// var_export(SpriteSheet::addGridToSheet("/_TEST.png", TRUE));
// print "\n\n";


// Get the parameters passed in from the PHP command line.
$shortopts = 'f:'; // Required
$longopts = array(
  'filename:', // Required
);
$opts = getopt($shortopts, $longopts);

// If no version is sent, we're done.
if (empty($opts['f'])) {
  print "Please add a filename to add grid lines to. Example:  php bin/spritesheet-grids.php -f altars.png\n";
  exit;
}

// Get the filename.
$filename = $opts['f'];

print "\nAdding grids to: " . $filename;

$urls = SpriteSheet::addGridToSheet("/" . $filename, TRUE);

if ($urls === FALSE) print "\nCould not find file: " . $filename;
if (empty($urls)) print "\nCould not create grids for: " . $filename;

print "\nFiles created at:";
if (!empty($urls['url'])) print "\n    " . $urls['url'];
if (!empty($urls['debug'])) print "\n    " . GAME_SERVER_PUBLIC_URL . $urls['debug'];

print "\n\nDone!";