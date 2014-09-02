# Introduction

Frontcontroller receives all incoming requests, and allows you to route these to various modules.

Example modules:

- CMS / Template renderer 
- CDN for static assets
- Redirect controllers
- Load balancer
- ... etc

# Installation

## Install dependencies 

    composer install
    
## Starting server

    cd web
    php -S 0.0.0.0:54321

now open localhost:54321 in your browser
