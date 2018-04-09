# Middleware

Starch is built around middleware. The concept is not new and I'll refer to the [PSR-15 spec](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-15-request-handlers.md) 
to explain what they are.

> A middleware component is an individual component participating, often together with other middleware components, 
in the processing of an incoming request and the creation of a resulting response, as defined by PSR-7.
>    
> A middleware component MAY create and return a response without delegating to a request handler, if sufficient 
conditions are met.

## Writing Middleware

Simply implement the `MiddlewareInterface` on your middleware class. Make sure to either return a response early, or 
delegate to the next request handler. 

```php
class ExceptionHandlingMiddleware implements MiddlewareInterface 
{
    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            return $handler->handle($request);
        } catch (Exception $exception) {
            return $response
                ->withStatus(500)
                ->withJson(['error' => $exception->getMessage()]);
        }
    }
}
```

## Order of execution

Starch will execute middleware in a FIFO (First In, First Out) manner. Imagine the following set up:

```php
$app = new Application($container);
// ... 

$app->add(ExceptionHandlingMiddleware::class);
$app->add(AuthenticationMiddleware::class);

$app->get('/hello-world', HelloWorldHandler::class);

$app->run();
```

When running the app, the request will first hit the ExceptionHandlingMiddleware, which wants to be the outer-most layer
layer to catch any and all exceptions. When ExceptionHandlingMiddleware calls it's next RequestHandler, that will be 
internally route the request to the next middleware, AuthenticationMiddleware in this case. 

As AuthenticationMiddleware is the last middleware of the stack, it's request handler will refer to the route request 
handler. 

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

- [Usage](docs/usage.md)
- [Using your own container](docs/containers.md)
- [Middleware](docs/middleware.md)
- [Components bound together by Starch](docs/components.md)
- [Comparison to other frameworks](docs/comparison.md)
