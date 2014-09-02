<?php

namespace FrontController\Module\TwigTemplate;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use FrontController\Core\ModuleInterface;

class TwigTemplateModule implements ModuleInterface
{
    public function handle(Application $app, Request $request, $template = null)
    {
        $html = $app['twig']->render($template, array());
        return $html;
    }
}
