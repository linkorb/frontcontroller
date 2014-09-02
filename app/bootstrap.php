<?php

use FrontController\Application;
use Symfony\Component\HttpFoundation\Request;

/** show all errors! */
ini_set('display_errors', 1);
error_reporting(E_ALL);

$app = new Application();

// General
$app->get(
    '/',
    'FrontController\Module\Website\WebsiteController::staticPageAction'
)->value("template", "@Website/frontpage.html.twig");

return $app;
