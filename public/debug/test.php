<?php

require_once('../../bootstrap.php');

$updater = Agnate\RPG\Updater::get();

d($updater->versionDiff('0.0.1'));