<?php

use FrontController\Application;
use Symfony\Component\HttpFoundation\Request;

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

$app->before(function (Request $request, Application $app) {
    $filter = new Twig_SimpleFilter('markdown', function ($value) use ($app) {
        $p = new Parsedown();

        return $p->text($value);
    });
    $app['twig']->addFilter($filter);
});

$controllerresolver = new \FrontController\ControllerResolver($app, null);
$app['resolver'] = $controllerresolver;

return $app;
