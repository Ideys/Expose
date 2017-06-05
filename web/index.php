<?php

require_once __DIR__.'/../vendor/autoload.php';

define('WEB_DIR', __DIR__);
define('TABLE_PREFIX', 'expose_');

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/controllers.php';
$app->run();
