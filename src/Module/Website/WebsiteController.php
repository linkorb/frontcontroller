<?php
namespace FrontController\Module\Website;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class WebsiteController
{
    public function staticPageAction(Application $app, Request $request, $template = null)
    {
        $html = $app['twig']->render($template, array());
        return $html;
    }
}
