# Versyx Framework

> **NOTE** This repository contains the core code of the Versyx framework, if you want to build an application using Versyx, visit the main [Versyx repository](#).

Versyx is a lightweight PHP framework suitable for developing web applications. It is a small-yet-powerful framework that comes with many features to aid in development, such as:

- A powerful dependency injection container.
- Built-in routing with support for API and web routes.
- Session management with support for multiple drivers.
- PSR-7 compliant request handling and view rendering.
- PSR-3 compliant application logging.


## The Service Container

The service container lies at the heart of Versyx, it is responsible for managing class dependencies and making making them available for dependency injection, which ensures that route handlers are ready to act when requests are received.

## Service Providers

Service Providers are responsible for registering dependencies or "services" into the service container. What we mean by "registering" is creating a new instance of a service, and then binding that instance to the container using either a string identifer, a fully-qualified class name (FQCN), or an interface.

For example, here is a simple demo custom class responsible for making API requests from an application that is built using the Versyx framework.

```php
namespace MyApp;

class ApiClient 
{
    protected array $config = [];

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function get(string $endpoint) {
        $response = $request->get($endpoint);
        return $response->getBody();
    }
}
```

To register this class as a service in the container, a service provider would be created.

```php
namespace MyApp\Providers;

use Versyx\Provider;
use Versyx\Service\ServiceProviderInterface;

class ApiClientServiceProvider implements ServiceProviderInterface
{
    public function __construct(Container $container) {
        $config = [
            'base_uri' => env('API_BASE_URI')
             ...
        ];

        // Bind to container using FQCN
        $container[ApiClient::class] = new ApiClient($config);

        return $container;
    }
}
```

This service provider would be called in the application's [bootstrap script](https://github.com/versyxdigital/versyx/blob/main/bootstrap.php) (please note the bootstrap script resides in the web starter project, not this framework core code repository).

```php
/*----------------------------------------
 | Create service container               |
 ----------------------------------------*/
$app = new Versyx\Service\Container();

/*----------------------------------------
 | Register service providers             |
 ----------------------------------------*/
$app->register(new Versyx\Providers\AppServiceProvider());
$app->register(new MyApp\Providers\ApiClientServiceProvider());
```

After the service is registered, it is available for dependency injection. For example, to use it in an application's `HomeController`.

```php
namespace MyApp\Http\Controllers;

use Versyx\Http\AbstractController;
use MyApp\ApiClient;

class HomeController extends AbstractController
{
    // ApiClient instance is injected into route method handler
    public function index(ApiClient $client) {
        $exchangeRates = $client->get('currency/gbp/exchange');
        
        return $this->view('home', [
            'exchangeRates' => $exchangeRates
        ]);
    }
}
```

### Service Locator vs Dependency Injection

Service locator and dependency injection are both design patterns used for managing dependencies and both are supported by Versyx.

#### Dependency Injection

In the example above, we used dependency injection, the example `ApiClient` object was retrieved through the dependent `HomeController`'s `index()` method, the dependency was *injected* into the class method.

```php
public function index(ApiClient $client) {
    ...
```

#### Service Locator

Service locator is a pattern where a "central registry", known as the service locator, is used to retrieve services and dependencies. A service locator provides a global point of access to a service.

Services still need to be registered to the container, however, the way they are retrieved is different. Versyx provides a global `app()` helper function to retrieve services using the service locator pattern.

Here is the same `HomeController` example, using the service locator pattern.

```php
namespace MyApp\Http\Controllers;

use Versyx\Http\AbstractController;
use MyApp\ApiClient;

class HomeController extends AbstractController
{
    public function index() {
        $exchangeRates = app(ApiClient::class)->get('currency/gbp/exchange');
        
        return $this->view('home', [
            'exchangeRates' => $exchangeRates
        ]);
    }
}
```

#### Which should you use?

Both patterns have their place in software development, but dependency injection is generally preferred due to its advantages in decoupling, testability, and clarity. Service locator can be useful in scenarios where centralising the management of dependencies is necessary, but it should be used with caution due to its tendency to obscure dependencies and increase coupling.

## Routing

Verysx uses [nikic/fastroute](#) under the hood for routing.

Routes are configured in the [web project's](#) `routes/` directory. There are two sets of files:

- `routes/api.php`: For API routes, these routes will start with the `/api/` prefix.
- `routes/web.php`: For web routes, these routes are not automatically prefixed.

Routes are typically structured in the following format (using a `routes/web.php` example):

```php
return [
    '/' => [
        ['GET', '/', [App\Http\Controllers\HomeController::class, 'index']]
    ],

    '/cms' => [

        ['GET', '/', [App\Http\Controllers\CMS\HomeController::class, 'index']],

        'users' => [
            ['GET',    '/',            [App\Http\Controllers\CMS\UserController::class, 'index']],
            ['POST',   '/create',      [App\Http\Controllers\CMS\UserController::class, 'store']],
            ['PUT',    '/edit/{id}',   [App\Http\Controllers\CMS\UserController::class, 'update']],
            ['DELETE', '/delete/{id}', [App\Http\Controllers\CMS\UserController::class, 'delete']],
        ],

        'pages' => [
            ['GET',    '/',            [App\Http\Controllers\CMS\PageController::class, 'index']],
            ['POST',   '/create',      [App\Http\Controllers\CMS\PageController::class, 'store']],
            ['PUT',    '/edit/{id}',   [App\Http\Controllers\CMS\PageController::class, 'update']],
            ['DELETE', '/delete/{id}', [App\Http\Controllers\CMS\PageController::class, 'delete']],
        ],
    ],
];
```

Routes consists of arrays, where keys are URL path segments, which are mapped to arrays within consisting of:

- The HTTP method (e.g. GET, POST, PUT, DELETE).
- The path to attach to the URL segment, with optional parameters.
- An array containing the route handler class name and method.

Route handlers (i.e. application controllers) are mapped to the service container along with their dependencies through the application Resolver
