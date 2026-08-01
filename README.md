![phpstan-level](https://img.shields.io/badge/PHPStan-Level%209-brightgreen)

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
  public static function factory(ContainerInterface $container): Cache
  {
    $config = $container->get(Config::class);

    return new Cache(
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