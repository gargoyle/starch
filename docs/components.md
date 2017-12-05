# Components bound together by Starch

The following components are used to provide a coherent whole

- [zendframework/zend-diactoros](https://github.com/zendframework/zend-diactoros) provides the PSR-7 interfaces
- [nikic/fast-route](https://github.com/nikic/FastRoute) is used for the routing
- [mindplay/middleman](https://github.com/mindplay-dk/middleman) provides the middleware processing

## How it works

- You construct a new Application with a container of your choice
- You (optionally) add routes and middleware
- You call `app->run()`, which will construct a request from globals, process the request and emit the returned response
- To process the request to get a response:
    - It's dispatched to the Router, which is just a simple wrapper for nikic's FastRoute. If the request matches a 
    route, the request gets tagged with the route as attribute and is returned
    - Upon errors in the dispatcher, appropriate HttpExceptions are thrown
    - The middleware stack is filtered based on the matched route
    - The request is sent through the middleware stack

**Read more:**

- [Installation and Usage](docs/usage.md)
- [Using your own container](docs/containers.md)
- [Middleware](docs/middleware.md)
- [Components bound together by Starch](docs/components.md)
- [Comparison to other frameworks](docs/comparison.md)
