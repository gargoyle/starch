# Usage

- Create an instance of your favorite PSR-11 compatible container and use it to create a new `Application`.
- Map `RequestHandlerInterface`s to paths. You can provide class instances or FQN's which will be loaded from the 
container.
- Add [middleware](middleware.md) as you need them. Same as request handlers, you can add `MiddlewareInterface` 
instances or FQNs.
- `run()` the app.

```php
// index.php
$builder = new ContainerBuilder();
// $builder->addDefinitions();
$container = $builder->build();

$app = new Application($container);

$app->get('/hello-world', new class implements RequestHandlerInterface {
   public function handle(ServerRequestInterface $request): ResponseInterface
   {
       return new TextResponse('Hello, world!');
   }
});

$app->get('/hello/{name}', HelloNameHandler::class);

$app->add(ExceptionHandlingMiddleware::class);
$app->add(CorsMiddleware::class);
$app->add(AuthenticationMiddleware::class);

$app->run();

// HelloNameHandler.php
class HelloNameHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new TextResponse(sprintf('Hello, %s!', $request->getAttribute('name'));
    }
}

```
 
**Read more:**

- [Usage](docs/usage.md)
- [Using your own container](docs/containers.md)
- [Middleware](docs/middleware.md)
- [Components bound together by Starch](docs/components.md)
- [Comparison to other frameworks](docs/comparison.md)
