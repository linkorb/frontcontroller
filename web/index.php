<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__.'/../vendor/autoload.php';

if (!file_exists(dirname(__DIR__) . '/.env')) {
    exit('Please copy .env.dist to .env, and adjust the settings.');
}
$dotenv = new Dotenv();
$dotenv->load(dirname(__DIR__) . '/.env');

Debug::enable();

$app = require_once __DIR__ . '/../app/bootstrap.php';

if (getenv('FRONTCONTROLLER_DEBUG')) {
    $app['debug'] = getenv('FRONTCONTROLLER_DEBUG')=='true';
}

require __DIR__.'/../src/error-controllers.php';

$request = Request::createFromGlobals();
$app->run($request);
