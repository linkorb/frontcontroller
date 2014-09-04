<?php

namespace FrontController\Module\TwigTemplate;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use FrontController\Core\ModuleInterface;

class TwigTemplateModule implements ModuleInterface
{
    public function handle(Application $app, Request $request, $template = null, $data = array())
    {
        $templatedata = array();
        
        foreach ($data as $datakey => $datavalue) {
            $ds = $app['frontcontroller.datasource.' . $datavalue['datasource']];
            $templatedata[$datakey] = $ds->getData(array('path' => $datavalue['path']));
        }

        $html = $app['twig']->render($template, $templatedata);

        return $html;
    }
}
