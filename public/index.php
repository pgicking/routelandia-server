<?php
// Default to NOT running in production. Override this by setting $GLOBALS['RUN_IN_PRODUCTION']=true in your local_config.php
if(!isset($GLOBALS['RUN_IN_PRODUCTION'])) { $GLOBALS['RUN_IN_PRODUCTION'] = false; }

require_once '../vendor/restler.php';
require_once "../database.php";

use Luracast\Restler\Restler;

// Config stuff
date_default_timezone_set('America/Los_Angeles');

$r = new Restler($GLOBALS['RUN_IN_PRODUCTION']);  

$r->addAPIClass('Highways');
$r->addAPIClass('Stations');
$r->addAPIClass('Resources'); //this creates resources.json at API Root
$r->addAPIClass('TrafficStats');
$r->addAPIClass('Detectors');
$r->handle();

