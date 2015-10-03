# Expressive Slim Router

[![Build Status](https://travis-ci.org/acelaya/expressive-slim-router.svg?branch=master)](https://travis-ci.org/acelaya/expressive-slim-router)
[![Code Coverage](https://scrutinizer-ci.com/g/acelaya/expressive-slim-router/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/acelaya/expressive-slim-router/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/acelaya/expressive-slim-router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/acelaya/expressive-slim-router/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/acelaya/expressive-slim-router/v/stable.png)](https://packagist.org/packages/acelaya/expressive-slim-router)
[![Total Downloads](https://poser.pugx.org/acelaya/expressive-slim-router/downloads.png)](https://packagist.org/packages/acelaya/expressive-slim-router)
[![License](https://poser.pugx.org/acelaya/expressive-slim-router/license.png)](https://packagist.org/packages/acelaya/expressive-slim-router)

A router for Zend Expressive based on Slim framework's implementation.

I decided to do this implementation because Slim's router supports optional params at the beginning of the path, while other Expressive supported routers don't.

## Installation

Install this package with composer

`composer require acelaya/expressive-slim-router`

## Usage

You just need to register the provided factory in your services configuration, and then the `ApplicationFactory` will take care of it.

**With Zend\ServiceManager:**

```php
use Acelaya\Expressive\Factory\SlimRouterFactory;
use Zend\Expressive\Router\RouterInterface;

return [
    'factories' => [
        RouterInterface::class => SlimRouterFactory::class
    ]
    // [...]
]
```

**With Aura.Di:**

```php
use Acelaya\Expressive\Factory\SlimRouterFactory;
use Aura\Di\Container;
use Aura\Di\ContainerConfig;
use Zend\Expressive\Router\RouterInterface;

class Common extends ContainerConfig
{
    public function define(Container $di)
    {
        $di->set(
            RouterInterface::class,
            $di->lazyGetCall(SlimRouterFactory::class, '__invoke', $di)
        );
        // [...]
    }
}
```

**With Pimple interop:**

```php
use Acelaya\Expressive\Factory\SlimRouterFactory;
use Interop\Container\Pimple\PimpleInterop as Pimple;
use Zend\Expressive\Router\RouterInterface;

$container = new Pimple();
$container[RouterInterface::class] = new SlimRouterFactory();
// [...]

return $container;
```
