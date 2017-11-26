<?php

namespace FrontController;

use Silex\Application as SilexApplication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Parser as YamlParser;
use RuntimeException;
use FrontController\Datasource\RestDatasource;

class Application extends SilexApplication
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->configureParameters();
        $this->configureApplication();
        $this->configureProviders();
        $this->configureRoutes();
  }

    private function configureParameters()
    {
        //$this['debug'] = true;
    }

    private function configureApplication()
    {
        switch (getenv('FRONTCONTROLLER_DATASOURCE_TYPE')) {
            case '':
                // No datasource, that's fine
                break;
            case 'rest':
                $config = [
                    'baseurl' => getenv('FRONTCONTROLLER_DATASOURCE_URL'),
                    'username' => getenv('FRONTCONTROLLER_DATASOURCE_USERNAME'),
                    'password' => getenv('FRONTCONTROLLER_DATASOURCE_PASSWORD'),
                ];
                $datasource = new RestDatasource($config);
                $this['frontcontroller.datasource'] = $datasource;
                break;
            default:
                throw new RuntimeException("Unsupported datasource type: " . $dsconfig['type']);
        }
    }


    private function configureRoutes()
    {
        $locator = new FileLocator(array($this['frontcontroller.basepath']));
        $loader = new YamlFileLoader($locator);
        $this['routes']->addCollection($loader->load('routes.yml'));
    }

    private function configureProviders()
    {
        // *** Setup Routing ***
        $this->register(new \Silex\Provider\RoutingServiceProvider());

        // *** Setup Twig ***
        $this->register(new \Silex\Provider\TwigServiceProvider());

        $options = array();
        $loader = null; // TODO
        $twig = new \Twig_Environment($loader, $options);

        $this['twig.loader.filesystem']->addPath(__DIR__ . '/../templates', 'Frontcontroller');
        $this['twig.loader.filesystem']->addPath($this['frontcontroller.basepath'] . '/templates', 'Website');
    }
}
