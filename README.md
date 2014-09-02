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
    php -t web -S 0.0.0.0:54321 web/routing.php

now open localhost:54321 in your browser
