# Starch


[![Build Status](https://img.shields.io/travis/starchphp/starch.svg?style=flat-square)](https://travis-ci.org/starchphp/starch)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/starchphp/starch.svg?style=flat-square)](https://scrutinizer-ci.com/g/starchphp/starch/?branch=master)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/starchphp/starch.svg?style=flat-square)](https://scrutinizer-ci.com/g/starchphp/starch/?branch=master)

Starch is a highly opinionated micro-framework that binds a bunch of PSR compatible components together.

<p align="center">
<img src="https://imgur.com/0RtqG1Q.png" />
</p>

<small>Logo by [Pencil](https://thenounproject.com/pencil/) from Noun project</small>

## Documentation

### Installation

To install Starch, you need a minimum of two packages:

1. Starch itself
2. A PSR-11 compatible container.

```shell
composer require starchphp/starch
composer require php-di/php-di
```

[PHP-DI](http://php-di.org/) was used as an example here. Further code examples will use PHP-DI as well.

### Read more in the [docs](docs):

- [Usage](docs/usage.md)
- [Using your own container](docs/containers.md)
- [Middleware](docs/middleware.md)
- [Components bound together by Starch](docs/components.md)
- [Comparison to other frameworks](docs/comparison.md)
