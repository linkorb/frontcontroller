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

    $filter = new Twig_SimpleFilter('defined', function ($value) use ($app, $request) {
        foreach ($request->attributes->all() as $k => $v) {
            if (is_string($v)) {
                $value = str_replace('{' . $k . '}', $v, $value);
            }
        }
        if ($request->attributes->has('variables')) {
            $variables = $request->attributes->get('variables');
            return isset($variables[$value]);
        }
        return false;
    });
    $app['twig']->addFilter($filter);


    $filter = new Twig_SimpleFilter('variable', function ($value) use ($app, $request) {
        foreach ($request->attributes->all() as $k => $v) {
            if (is_string($v)) {
                $value = str_replace('{' . $k . '}', $v, $value);
            }
        }
        if ($request->attributes->has('variables')) {
            $variables = $request->attributes->get('variables');
            if (isset($variables[$value])) {
                return $variables[$value];
            } else {
                return '?' . $value . '?';
            }
        }
        return '?no-variables?';
    });
    $app['twig']->addFilter($filter);

    $ds = $app['frontcontroller.datasource'];
    $data = $request->attributes->get('data');
    if ($data) {
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
            $request->attributes->set($name, $values);
        }
    }
});

$controllerresolver = new \FrontController\ControllerResolver($app, null);
$app['resolver'] = $controllerresolver;

return $app;
