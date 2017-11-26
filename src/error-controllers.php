<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        '@Website\\error-'.$code.'.html.twig',
        '@Website\\error-'.substr($code, 0, 2).'x.html.twig',
        '@Website\\error-'.substr($code, 0, 1).'xx.html.twig',
        '@Website\\error-default.html.twig',
        '@Frontcontroller\\error-default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
