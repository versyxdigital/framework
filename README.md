# Versyx Framework

> **NOTE** This repository contains the core code of the Versyx framework, if you want to build an application using Versyx, visit the main [Versyx repository](#).

Versyx is a lightweight PHP framework suitable for developing web applications. It is a small-yet-powerful framework that comes with many features to aid in development, such as:

- A powerful dependency injection container.
- Built-in routing with support for API and web routes.
- PSR-7 compliant request handling and view rendering.
- PSR-3 compliant application logging.

## Application Structure

Versyx is structured as follows:

```
.
├── docker/                 # Development docker build files
├── src/                    # Framework source code
│   ├── Exception/          # Error exceptions
│   ├── Providers/          # Framework service providers
│   ├── Service/            # Service container and provider contract
│   ├── View/               # View template engine and contract
│   ├── Controller.php      # Base HTTP route handler controller
│   ├── Helpers.php         # Framework helper functions
│   ├── Kernel.php          # Bootstraps app and handles request-response
│   ├── Request.php         # Handles HTTP requests and server request objects
│   ├── RequestFactory.php  # Creates server request objects from HTTP requests
│   ├── Resolver.php        # Dependency injection and route handler resolver
├── vendor                  # Reserved for Composer
├── composer.json           # Composer configuration
├── LICENSE                 # The license
└── README.md               # This file
```

## The Service Container

The service container lies at the heart of Versyx, it is responsible for managing class dependencies and making making them available for dependency injection. Dependency injection allows us to inject dependencies into classes via the constructor, or in some cases, "setter" methods.

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
$app->register(new Versyx\Providers\LogServiceProvider());
$app->register(new Versyx\Providers\RouteServiceProvider());
$app->register(new Versyx\Providers\ViewServiceProvider());

$app->register(new MyApp\Providers\ApiClientServiceProvider());
```

After the service is registered, it is available for dependency injection. For example, to use it in an application's `HomeController`.

```php
namespace MyApp\Controllers;

use Versyx\Controller;
use MyApp\ApiClient;

class HomeController extends Controller
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
namespace MyApp\Controllers;

use Versyx\Controller;
use MyApp\ApiClient;

class HomeController extends Controller
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