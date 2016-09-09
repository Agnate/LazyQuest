<?php
// Add these to composer's requires if they aren't already there:
// "devristo/phpws": "dev-master"
// "frlnc/php-slack": "*"

require_once('bootstrap.php');

use Agnate\LazyQuest\Server;

$server = new Agnate\LazyQuest\Server;
$server->start();