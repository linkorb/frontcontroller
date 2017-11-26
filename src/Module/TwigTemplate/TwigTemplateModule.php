<?php

namespace FrontController\Module\TwigTemplate;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use FrontController\Core\ModuleInterface;

class TwigTemplateModule implements ModuleInterface
{
    public function handle(Application $app, Request $request, $template = null, $data = [])
    {
        $input = array();
        $ds = $app['frontcontroller.datasource'];

        foreach ($data as $name => $config) {
            $path = $config['path'];

            // Process `path` with url parameters
            foreach ($request->attributes->all() as $key => $value) {
                if (is_string($value)) {
                    $path = str_replace('{' . $key . '}', $value, $path);
                }
            }
            // fetch data from data-source
            $values = $ds->getData(array('path' => $path));

            // Optionally use offset in data
            if (isset($config['offset'])) {
                $values = $values[$config['offset']];
            }

            // Optionally re-key
            if (isset($config['key'])) {
                $res = [];
                foreach ($values as $key => $value) {
                    $newKey = $value[$config['key']];
                    if (isset($config['value'])) {
                        $value = $value[$config['value']];
                    }
                    $res[$newKey] = $value;
                }
                $values = $res;
            }

            if (isset($config['mode'])) {
                switch ($config['mode']) {
                    case 'first':
                        $values = $values[0];
                        break;
                    case 'last':
                        break;
                    case 'all':
                    default:
                        break;
                }
            }
            $input[$name] = $values;
        }

        // print_r($input);exit();
        $html = $app['twig']->render($template, $input);

        return $html;
    }
}
