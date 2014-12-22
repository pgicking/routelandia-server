<?php

require_once '../vendor/restler.php';

use Luracast\Restler\Restler;

$r = new Restler();
$r->addAPIClass('Highway');
$r->handle();

