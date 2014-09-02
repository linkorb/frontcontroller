<?php
namespace FrontController\Module\StaticFile;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use FrontController\Core\ModuleInterface;

class StaticFileModule implements ModuleInterface
{
    public function handle(Application $app, Request $request)
    {
        $basedir = $request->attributes->get('basedir');
        if ($basedir[0]!='/') {
            // relative path
            $basedir = $app['frontcontroller.basepath'] . '/' . $basedir;
        }
        
        $basedir = realpath($basedir);
        $postfix = $request->attributes->get('postfix');
        
        // Sanity check
        if (trim($basedir, '/') == '') {
            return $this->errorResponse('Service unavailable', 503);
        }
        // Create absolute filename
        $filename = realpath($basedir . '/' . $postfix);

        if (!file_exists($filename)) {
            return $this->errorResponse('File not found', 404);
        }

        // Ensure absolute filename is subdir of basedir
        if (substr($filename, 0, strlen($basedir))!=$basedir) {
            return $this->errorResponse("Forbidden", 403);
        }

        // TODO: Add more control over the caching headers
        $response = new Response(
            file_get_contents($filename),
            Response::HTTP_OK,
            array(
                'content-type' => $this->filenameToContentType($filename),
                'Expires' => 'Mon, 04 Jul 2078 12:00:00 GMT',
                'Cache-Control' => 'public, max-age=315360000',
                'Vary' => 'Accept-Encoding'
            )
        );
        
        return $response;
    }

    private static function fileNameToContentType($filename)
    {
        $pathinfo = pathinfo($filename);
        $extension = $pathinfo['extension'];
        switch (strtolower($extension)) {
            case "gif":
                $content_type = "image/gif";
                break;
            case "png":
                $content_type = "image/png";
                break;
            case "svg":
                $content_type = "image/svg+xml";
                break;
            case "jpg":
            case "jpeg":
                $content_type = "image/jpeg";
                break;
            case "tif":
                $content_type = "image/tif";
                break;
            case "msg":
            case "eml":
                $content_type = "message/rfc822";
                break;
            case "xls":
                $content_type = "application/vnd.ms-excel";
                break;
            case "zip":
                $content_type = "application/zip";
                break;
            case "rdp":
                $content_type = "application/rdp";
                break;
            case "pdf":
                $content_type = "application/pdf";
                break;
            case "css":
                $content_type = "text/css";
                break;
            case "html":
            case "htm":
                $content_type = "text/html";
                break;
            case "txt":
            case "csr":
                $content_type = "text/plain";
                break;
            case "xml":
                $content_type = "text/xml";
                break;
            case "js":
                $content_type = "application/javascript";
                break;
            case "ttf":
                $content_type = "font/ttf";
                break;
            default:
                $content_type = "application/x-download";
                break;
        }

        return $content_type;
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
