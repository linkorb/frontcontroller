<?php

use FrontController\Application;

/** show all errors! */
ini_set('display_errors', 1);
error_reporting(E_ALL);

$basepath = getenv('FRONTCONTROLLER_BASEPATH');
if ($basepath == '') {
    throw new RuntimeException('Please provide the enviroment variable `FRONTCONTROLLER_BASEPATH`');
}

$app = new Application(
    array (
        'frontcontroller.basepath' => $basepath
    )
);
$controllerresolver = new \FrontController\ControllerResolver($app, null);
$app['resolver'] = $controllerresolver;

return $app;
