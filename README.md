# Introduction

Frontcontroller receives all incoming requests, and allows you to route these to various modules.

For each route (url), you can specify a module to handle the request.

Example modules:

- Twig: CMS / Template renderer 
- File: serve static files (images, css, js, etc)
- Redirect: respond with different types of redirect headers
- ReverseProxy: Pass requests on to backend servers
- ... etc

It's easy to add new modules that can handle new types of requests

# Installation

## Install dependencies 

    composer install
    
## Starting server in development mode

    cd web
    export FRONTCONTROLLER_BASEPATH=/my/frontcontroller/website/
    php -d variables_order=EGPCS -t web -S 0.0.0.0:54321 web/index.php

Now open localhost:54321 in your browser.
    
# Troubleshooting

## No images / scripts / assets using PHP built-in server

You need to explicitly add `web/index.php` to your startup command,
otherwise the built-in server won't support files with `.` characters in them.
