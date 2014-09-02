<?php

namespace FrontController;

use Silex\Application as SilexApplication;
use Symfony\Component\HttpFoundation\Request;

class Application extends SilexApplication
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->configureParameters();
        $this->configureProviders();
        $this->configureServices();
        $this->configureSecurity();
        $this->configureListeners();
    }
    
    private function configureParameters()
    {
        $this['debug'] = true;

        $this['apiregistry.baseurl'] = 'http://localhost:9321/';
    }
    
    private function configureProviders()
    {
        // *** Setup Translation ***
        $this->register(new \Silex\Provider\LocaleServiceProvider());
        $this->register(new \Silex\Provider\ValidatorServiceProvider());
        $this->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'translator.messages' => array(),
        ));
        // *** Setup Form ***
        $this->register(new \Silex\Provider\FormServiceProvider());
        
        // *** Setup Twig ***
        $this->register(new \Silex\Provider\TwigServiceProvider());
        
        $options = array();
        $loader = null; // TODO
        $twig = new \Twig_Environment($loader, $options);
                
        $this['twig.loader.filesystem']->addPath(__DIR__ . '/../example/website/views', 'Website');
        //$this['twig.loader.filesystem']->addPath(__DIR__ . '/../app/Resources/views', 'App');

        // *** Setup Sessions ***
        $this->register(new \Silex\Provider\SessionServiceProvider(), array(
            'session.storage.save_path' => '/tmp/frontcontroller_sessions'
        ));

        // *** Setup Routing ***
        $this->register(new \Silex\Provider\RoutingServiceProvider());

        // *** Setup Doctrine DBAL ***
        /*
        $this->register(new \Silex\Provider\DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver'   => 'pdo_mysql',
                    'host'      => $this['db.config.server'],
                    'dbname'    => $this['db.config.name'],
                    'user'      => $this['db.config.username'],
                    'password'  => $this['db.config.password'],
                    'charset'   => 'utf8',
            ),
        ));
        */

        // *** Setup Doctrine ORM ***
        /*
        $this->register(new DoctrineOrmServiceProvider, array(
            "orm.proxies_dir" => "/path/to/proxies",
            "orm.em.options" => array(
                "mappings" => array(
                    array(
                        "type" => "annotation",
                        "namespace" => "SuperBase\Entities",
                        "path" => __DIR__."/../../src/ApiRegistry/Entities",
                    )
                ),
            ),
        ));
        */
    }
    
    private function configureServices()
    {
        
    }
    
    private function configureSecurity()
    {
        // Setup Security
        
        /*
        $this->register(new \Silex\Provider\SecurityServiceProvider(), array());

        $this['security.firewalls'] = array(
            'dashboard' => array(
                'anonymous' => false,
                'pattern' => '^/dashboard',
                'form' => array('login_path' => '/login', 'check_path' => '/dashboard/login_check'),
                'logout' => array('logout_path' => '/dashboard/logout'),
                'users' => new \ApiRegistry/UserProvider($this['db'], null),
            )
        );
        */
    }
    
    private function configureListeners()
    {
        // todo
    }
}
