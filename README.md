# Introduction

Frontcontroller is a super light weight website serving application.

It receives all incoming requests, and allows you to route these to various modules.

For each route (url), you can specify a module to handle the request.

Example modules:

- Twig: CMS / Template renderer
- File: serve static files (images, css, js, etc)
- Redirect: respond with different types of redirect headers
- ReverseProxy: Pass requests on to backend servers
- Form: Submit a (contact) form and email the results
- ... etc

It's easy to add new modules that can handle new types of requests

# Installation

## Install dependencies

    composer install

## Configuration

Copy the provided `.env.dist` file to `.env`, and adjust for your situation.

Please refer to the example `.env.dist` file's comments to see what the configuration options mean.

## Creating a website

Websites are all managed in their own directories.

### routes.yml

This `routes.yml` file in the root of your website directory allows you to configure a list of urls,
and how you would like to handle them.

An example:

```yaml
frontpage:
    path: /
    defaults:
        _controller: TwigTemplate
        template: "@Website\\frontpage.html.twig"
contact:
    path: /contact
    defaults:
        _controller: TwigTemplate
        template: "@Website\\contact.html.twig"
```
This will enable `/` and `/contact` to work. Both routes will be served using a Twig template.

### templates/*.html.twig

You can store your Twig templates ([twig.symfony.com](https://twig.symfony.com)) in the `templates/` directory.
By creating routes for these templates you can view them.

### assets (static files)

You can store your images, javascript files, css files, etc in any subdirectory (i.e. `assets/`). By creating a route for
your assets they can be served. For example:

```yaml
assets:
    path: /assets/{postfix}
    defaults:  { _controller: StaticFile, basedir: assets/ }
    requirements:
        postfix: .*
```

This will allow your assets to be available on `/assets/my.png`. You can now use these assets in your (twig) templates.

You can create multiple routes for static assets, for example if you want to split your css, js and images into their own directories.

### Forms

You can easily create a route that handles your form submissions, and email them to a preconfigured recipient.

For example:

```yml
form-submit:
    path: /form-submit
    defaults:
        _controller: Form
        recipient: "joe@example.web"
        subject: "New call-me request from {firstname}"
```
You can now create a simple HTML5 form anywhere on your website and submit them to `/form`. For example:

```html
<h1>My call-me form:</h1>
<form method="POST" action="/form-submit">
    <!-- simply create and style the HTML5 form in any way you like -->
    <input type="text" name="firstname"  placeholder="Firstname" />
    <input type="text" name="phone" placeholder="Phone" />

    <!-- you can optionally pass hidden fields to be included in your emails -->
    <input type="hidden" name="my-hidden-field" value="This was submitted from the contact form" />
    <!-- after the form is submitted, redirect to /thank-you -->
    <input type="hidden" name="_target" value="/thank-you" />

    <!-- include a submit button -->
    <button type="submit">Submit</button>
</form>
```

### Datasources

You can include dynamic content such as blog-posts, pages, products, etc by defining a "Datasource".

A datasource can be any HTTP(s) URL that returns JSON. Often this is an API endpoint of a "headless" CMS (such as [directus](https://getdirectus.com/)), but a datasource can be any API server, as long as it returns JSON data.

Please refer to the example `.env.dist` to see how to configure your datasource.
Using the `.env` file you set up the BASEURL and optionally USERNAME and PASSWORD to make HTTP requests too.

Now for every route where you want to use the dynamic data, you define the "data".

In the example below we'll set up a dynamic blog, managed in Directus. In the same way you
could dynamically create routes and templates for things like products, team-members, etc etc.

```yml
blog:
    path: /blog
    defaults:
        _controller: TwigTemplate
        template: "@Website\\blog_index.html.twig"
        data:
            blogs: { path: /tables/blog/rows?order[id]=DESC }
```

You can now use the `blogs` variable (array) in your blog_index.html.twig template.

For example:

```html
<h1>My blogs</h1>
{% for blog in blogs.data %}
  <h2>{{ blogs.headline }}</h2>
  <p>{{blogs.summary}}</p>
  <a href="/blogs/{{ blog.uri }}">Read more...</a>
  <hr />
{% endfor %}
```

Let's create the blog view route:

```
blog_view:
    path: /blog/{blogUri}
    defaults:
        _controller: TwigTemplate
        template: "@Website\\blog_view.html.twig"
        data:
            blog: { path: "/tables/blog/rows?in[uri]={blogUri}", offset: data, mode: first }
```

Note how the `path` contains a variable (blogUri), that we'll use in the `data` definition as part of the `path`.

Also the `offset` is specified. This allows you to only fetch the data you need in case the returned
JSON data is wrapped in parent elements you don't really need. For example, directus returns the actual row data wrapped in `meta` and `data` keys. By specifying `offset: data`, the `blog` variable now only contains the real row data.

Normally the datasource returns an array of rows. In case you only need the first one, you simply specify `mode: first`.


In the `blog_view.html.twig` the `blog` array is now available:

```html
<h1>{{ blog.headline}}</h1>
<i>Posted at {{ blog.postedAt}} by {{ blog.author }}</i>
<p>{{ blog.content|markdown|raw }}</p>
```

Note how we're running the `blog.content` through a `markdown` filter, and a `raw` filter.

The `markdown` filter turns markdown content into HTML. The `raw` filter will unescape the HTML tags so they are properly displayed. You can find more filters and their documentation in the [Twig filter documentation](https://twig.symfony.com/doc/2.x/filters/index.html)



## Starting server in development mode

    php -S 0.0.0.0:54321 -t web web/index.php

Now open localhost:54321 in your browser.

# Troubleshooting

## No images / scripts / assets using PHP built-in server

You need to explicitly add `web/index.php` to your startup command,
otherwise the built-in server won't support files with `.` characters in them.

## License

MIT. Please refer to the [license file](LICENSE) for details.

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!
