<?php

require_once '../vendor/restler.php';
require_once "../database.php";

use Luracast\Restler\Restler;

$r = new Restler();  // Pass "true" to this to put Restler into production mode
$r->addAPIClass('Highways');
$r->addAPIClass('StationsController');
$r->addAPIClass('Resources'); //this creates resources.json at API Root
$r->addAPIClass('TrafficStats');
$r->addAPIClass('DetectorsController');
$r->handle();

