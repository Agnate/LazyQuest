<?php
// Add these to composer's requires if they aren't already there:
// "devristo/phpws": "dev-master"
// "frlnc/php-slack": "*"

require_once('bootstrap.php');

use Agnate\RPG\Server;

$server = new Agnate\RPG\Server;
$server->start();