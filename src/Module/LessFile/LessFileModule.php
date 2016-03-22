<?php
namespace FrontController\Module\LessFile;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use FrontController\Core\ModuleInterface;

class LessFileModule implements ModuleInterface
{
    public function handle(Application $app, Request $request)
    {
        $filename = $request->attributes->get('filename');
        if ($filename[0]!='/') {
            // relative path
            $filename = $app['frontcontroller.basepath'] . '/' . $filename;
        }
        
        $filename = realpath($filename);
        // Sanity check

        if (!$filename || !file_exists($filename)) {
            return $this->errorResponse('File not found', 404);
        }
        $content = file_get_contents($filename);
        
        $less = new \lessc();
        $css = $less->compile($content);

        // TODO: Add more control over the caching headers
        $response = new Response(
            $css,
            Response::HTTP_OK,
            array(
                'content-type' => "text/css",
                'Expires' => 'Mon, 04 Jul 2078 12:00:00 GMT',
                'Cache-Control' => 'public, max-age=315360000',
                'Vary' => 'Accept-Encoding'
            )
        );
        
        return $response;
    }

    private function errorResponse($text, $code)
    {
        $response = new Response(
            $text,
            $code,
            array('content-type' => 'text/html')
        );
        return $response;
    }
}
