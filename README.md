![phpstan-level](https://img.shields.io/badge/PHPStan-Level%209-brightgreen)
![pest-php](https://img.shields.io/badge/Tests-%20Passed-brightgreen)

# Container

A minimal and deterministic dependency injection container designed for developers who prefer explicit service definitions over autowiring or reflection‑based magic.

It provides a clean, predictable and high‑performance foundation for applications that value clarity, modularity, and full control over their initialization process.


## ✨ Features

- **PSR‑11 compatible**  
  Fully aligned with `Psr\Container\ContainerInterface`, ensuring interoperability with existing tooling.

- **Explicit service registration**  
  Register services using clear, intention‑revealing methods: `set()`, `bind()`, and `singleton()` — no autowiring, no magic.

- **Factory‑based instantiation**  
  Classes may implement `ServiceFactoryInterface` to control their own creation logic, ensuring predictable and testable instantiation.

- **Provider lifecycle management**  
  ServiceProviderInterface defines `register()`, `boot()`, and `terminate()` phases, enabling modular initialization and teardown.

- **Framework‑agnostic design**  
  Contracts do not assume any specific container implementation, allowing different ecosystems to adopt and extend them freely.

- **Minimal footprint**  
  The container and service manager are intentionally small, readable, maintainable, ideal for microframeworks, modular applications and high‑performance environments.

- **Deterministic resolution**  
  Services are resolved through one of three clear mechanisms: a closure, a factory class or direct instantiation. Nothing else is performed behind the scenes.

- **Zero magic, zero reflection**  
  The container does not attempt to guess dependencies or resolve classes automatically. Every service is defined explicitly, making the system easy to audit and reason about.


## 🧩 Factory Adapter Pattern

To keep libraries pure and decoupled from the container, this package embraces a simple but powerful idea: any external class can be adapted into a container‑aware service without modifying the original library.

By creating a small adapter class that:
- extends the original implementation
- implements ServiceFactoryInterface
- and exposes a static factory() method
- you gain full control over how the service is constructed — configuration, dependencies, initialization logic — while keeping the underlying library untouched.

This pattern allows any third‑party library to be integrated cleanly and predictably, making the container universally compatible without relying on autowiring or reflection.

---

## 📦 Installation

Install via Composer:

```bash
composer require stougeiro/container
```

## 🚀 Usage Example

### Registering services explicitly

```php
use STDW\Container\Container;

$container = new Container();

// Bind: always creates a new instance
$container->bind(CacheInterface::class, FileCache::class);
// Singleton: shared instance
$container->singleton(LoggerInterface::class, FileLogger::class);

/**
 * Retrieving services */
$logger = $container->get(LoggerInterface::class); // shared instance
$cache = $container->get(CacheInterface::class); // new instance
```

### Using a factory class

Any class may implement `ServiceFactoryInterface`:

```php
use STDW\Contract\Container\ServiceFactoryInterface;
use STDW\Contract\Container\ContainerInterface;

class CacheService extends Cache implements ServiceFactoryInterface
{
  public static function factory(ContainerInterface $container): static
  {
    $config = $container->get(Config::class);

    return new static(
      $config->get('cache.ttl', 3600),
      $config->get('cache.namespace', 'app'),
      $config->get('cache.path', '/tmp/app-cache')
    );
  }
}

/**
 * Registering */
$container->singleton(CacheInterface::class, CacheService::class);

/**
 * Retrieving services */
$cache = $container->get(CacheInterface::class); // shared instance
```

### Using a Service Provider

```php
class CacheServiceProvider implements ServiceProviderInterface
{
    public function __construct(
        protected ContainerInterface $container
    ) {}

    public function register(): void
    {
        // Register the cache service using the factory
        $this->container->singleton(
            CacheInterface::class,
            CacheService::class
        );
    }
}

/**
 * Registering the provider */
use STDW\Container\ServiceManager;

$manager = new ServiceManager($container);
$manager->add(CacheServiceProvider::class);

/**
 * Executing the provider lifecycle */
$manager->register();
$manager->boot();

  /**
   * Application runs
   * ----------------
   * Retrieving the cache service
   */ $cache = $container->get(CacheInterface::class);

$manager->terminate();
```

### Global Container Access

The package provides an optional global helper function called `container()`, which returns a single shared instance of the container. This enables a simple and convenient static mode, allowing services to be registered and resolved without manually instantiating the container.

The function is automatically loaded through Composer and can be used anywhere in the application.

```php
// Registering
container()->singleton(LoggerInterface::class, FileLogger::class);

// Retrieving services
$logger = container()->get(LoggerInterface::class);
```

It also integrates seamlessly with the `ServiceManager`:

```php
$manager = new ServiceManager(container());

// Registering the provider
$manager->add(AppProvider::class);

// Executing the provider lifecycle
$manager->register();
$manager->boot();

  // Application runs
  // the global container now contains all provider-registered services
  container()->get(SomeService::class); // Retrieving registered service

$manager->terminate();
```

Using the global container is entirely optional.
Developers who prefer isolated instances can continue using `new Container()` normally.

---

## 🧠 Why?

Modern containers often rely heavily on autowiring, reflection, and implicit behavior.
While convenient, these features can introduce unpredictability, hidden costs, and unnecessary complexity.

This container takes the opposite approach:
explicit definitions, deterministic behavior, and maximum clarity.

It is built for developers who value:
- full control over service creation
- predictable initialization flows
- modular provider‑based architecture
- zero hidden magic
- high performance with minimal overhead
- code that is easy to audit and reason about

If you prefer containers that do exactly what you tell them — no more, no less — this package is designed for you.

---

## 🤝 Contributions

Contributions are welcome.
Feel free to open issues or submit pull requests.

<br><br>

[<img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" width="170"/>](https://www.buymeacoffee.com/stougeiro)