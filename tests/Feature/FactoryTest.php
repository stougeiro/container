<?php

    use STDW\Contract\Container\ContainerInterface;
    use STDW\Contract\Container\ServiceFactoryInterface;
    use STDW\Container\Container;


    interface AppConfigInterface
    { }

    class AppConfig implements AppConfigInterface
    {
        public function __construct(
            public string $environment = 'production'
        ) {}
    }

    class ExternalService
    {
        public function __construct(
            public AppConfigInterface $config
        ) {}
    }

    class ExternalServiceFactory extends ExternalService implements ServiceFactoryInterface
    {
        public static function factory(ContainerInterface $container): static
        {
            return new static(
                $container->get(AppConfigInterface::class)
            );
        }
    }


    it('resolves services that need external dependencies through ServiceFactoryInterface', function () {
        $container = new Container();
        $container->singleton(AppConfigInterface::class, AppConfig::class);
        $container->singleton(ExternalService::class, ExternalServiceFactory::class);

        $service = $container->get(ExternalService::class);

        expect($service)
            ->toBeInstanceOf(ExternalService::class)
            ->and($service->config)
            ->toBeInstanceOf(AppConfig::class)
            ->and($service->config->environment)->toBe('production');
    });
