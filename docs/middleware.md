# Middleware

Starch is built around middleware. The concept is not new and I'll refer to the [PSR-15 proposal](https://github.com/php-fig/fig-standards/blob/master/proposed/http-handlers/request-handlers.md) 
to explain what they are.

> An middleware component is an individual component participating, often together with other middleware components, 
in the processing of an incoming request and the creation of a resulting response, as defined by PSR-7.
>    
> A middleware component MAY create and return a response without delegating to a request handler, if sufficient 
conditions are met.

## Writing Middleware

To read more about Middleware, I refer you to the documentation of [Mindplay's Middleman](https://github.com/mindplay-dk/middleman),
the package used in Starch to handle middleware execution.

## Constraining middleware to specific paths

In Starch, it is possible to limit Middleware to certain paths. E.g. AuthenticationMiddleware that should only be 
executed for an admin section of the application. You do this by providing the second, optional `pathConstraint` 
parameter when adding middleware.

```php
$app->add(AuthenticationMiddleware::class, '/admin');
```

Note that the path constraint refers to the path set in the route, not the request uri. The path constraint does a 
partial lookup as well: `/admin` will match, `/admin`, but also `/admin/management`.

**Read more:**

- [Installation and Usage](docs/usage.md)
- [Using your own container](docs/containers.md)
- [Middleware](docs/middleware.md)
- [Components bound together by Starch](docs/components.md)
- [Comparison to other frameworks](docs/comparison.md)
