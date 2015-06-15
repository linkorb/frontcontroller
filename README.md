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

# Auto-generation of Apache config
It is possible to generate Apache config files by using the command line tool.
The tool supports Apache config file generation for OSX and Debian/Ubuntu.
```
sudo app/console frontcontroller:apacheconf [path_to_website_root_dir] [path_to_web_root]
```
Note:
* The [path_to_web_root] is optional. If not specified, it uses the ```web``` directory of this repo.
If specified, [path_to_web_root] is a symlink to the ```web``` directory.
* In the websites' frontcontroller.yml file, it's needed to add ```host: example.com``` to make it work.
* On OSX, you need to manually include the new config file in ```/etc/apache2/httpd.conf
* On Debian/Ubuntu, you need to symlink the new config file in /etc/apache2/sites-enabled/
