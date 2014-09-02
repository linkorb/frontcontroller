<?php

namespace FrontController\Module\Redirect;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FrontController\Core\ModuleInterface;
use RuntimeException;

class RedirectModule implements ModuleInterface
{
    public function handle(Application $app, Request $request, $template = null)
    {
        $postfix = $request->attributes->get('postfix');
        if (!$request->attributes->has('target')) {
            throw new RuntimeException("This controller requires the `target` attribute to be defined");
        }
        $target = $request->attributes->get('target');
        $forwardpostfix = false;
        if ($request->attributes->has('forwardpostfix')) {
            $forwardpostfix = $request->attributes->get('forwardpostfix');
        }   

        $httpcode = '301';
        if ($request->attributes->has('httpcode')) {
            $httpcode = $request->attributes->get('httpcode');
        }
        
        switch($httpcode) {
            case 301: // Moved permanently 
            case 302: // Moved temporary
                break;
            default:
                throw new RuntimeException("Unconfigured or unsupported redirect httpcode");
                break;
        }
        $newurl = $target;
        
        if ($forwardpostfix) {
            $newurl .= $postfix;
        }
        foreach($request->query->all() as $key=>$value) {
            $first = true;
            if ($value) {
                if ($first) {
                    $newurl .='?';
                } else {
                    $newurl .= '&';
                }
                $newurl .= $key . '=' . $value;
            }
        }

        $response = new RedirectResponse(
            $newurl,
            $httpcode
        );

        // caching
        /*
        $date = new \DateTime();
        $date->modify('+1 day');
        $response->setExpires($date);
        */
        
        return $response;
    }
}
