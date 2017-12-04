# Comparison to other frameworks
 
 Other micro frameworks exist that handle middleware too. Here's a short overview of Starch features and where they 
 differ with a couple other existing micro-frameworks.
 
 ## Starch
 
 - Provides a single `->add()` interface to add middleware to the stack
 - Middleware are added to a unified queue where you control the execution order
 - PSR-15 compatible Middleware interfaces
 - First in, First out execution order
 
 ## [Slim](https://www.slimframework.com/)
 
 - `->add()` middleware to the app or route(group)s.
 - App middleware and route (group) middleware are added in different layers. Route middleware will always follow app 
 middleware
 - Uses non-PSR, "Double pass" interface. See [PSR-15 meta discussion](https://github.com/php-fig/fig-standards/blob/master/proposed/http-handlers/request-handlers-meta.md#4-request-handler-approaches)
 - First in, last out execution order
 
 ## [Silex](https://silex.symfony.com/) (now EOL)
 
 - Separate `->before()` and `->after()` methods depending on when the "middleware" must be executed in relation to 
 route handling
 - Full control over execution order
 - Different interfaces depending on before/after usage.
 - FIFO, with optional extra priority control 
 
 ## [Lumen](https://lumen.laravel.com/)
 
 - [Multiple ways of adding middleware](https://lumen.laravel.com/docs/5.5/middleware#registering-middleware)
 - PSR-15-like "single pass" interface, but not actually PSR-15 compatible
 - FIFO execution
 
 ## [Zend Expressive](https://docs.zendframework.com/zend-expressive/)
 
 - Multiple `->pipe()` methods to do app general middleware or route-bound middleware.
 - Older PSR-15 proposal implementation. Still uses DelegateInterface instead of RequestHandlerInterface.
 - FIFO execution
 
**Read more:**

- [Installation and Usage](docs/usage.md)
- [Using your own container](docs/containers.md)
- [Middleware](docs/middleware.md)
- [Components bound together by Starch](docs/components.md)
- [Comparison to other frameworks](docs/comparison.md)
