<?php

namespace FrontController\Module\TwigTemplate;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use FrontController\Core\ModuleInterface;

class TwigTemplateModule implements ModuleInterface
{
    public function handle(Application $app, Request $request, $template = null)
    {
        $input = array();
        foreach ($request->attributes->all() as $key=>$value) {
            $input[$key] = $value;
        }

        // print_r($input);exit();
        $html = $app['twig']->render($template, $input);

        return $html;
    }
}
