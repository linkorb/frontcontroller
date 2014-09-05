<?php

namespace FrontController\Datasource;

class RestDatasource implements DatasourceInterface
{
    public function __construct($config = array())
    {
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->baseurl = $config['baseurl'];
        $this->client = new \GuzzleHttp\Client();

    }
    
    public function getData($parameters = array())
    {
        $path = $parameters['path'];
        $url = $this->baseurl . $path;
        $res = $this->client->get($url, array(
            'auth' =>  [$this->username, $this->password]
        ));
        
        return json_decode($res->getBody(), true);
    }
}
