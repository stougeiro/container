<?php

    use STDW\Container\Container;
    use STDW\Container\ServiceManager;
    use STDW\Contract\Container\ContainerInterface;
    use STDW\Contract\Container\ServiceProviderInterface;


    class TestableServiceManager extends ServiceManager
    {
        public function getContainer(): ContainerInterface
        { return $this->container; }

        public function getCollection(): array
        { return $this->collection; }
    }

    /** 3 fake interfaces
     */
    interface AlphaInterface {}
    interface BetaInterface {}
    interface GammaInterface {}

    /** 3 fake implementations
     */
    class Alpha implements AlphaInterface {
        public function __construct(public string $value = 'alpha') {}
    }

    class Beta implements BetaInterface {
        public function __construct(public string $value = 'beta') {}
    }

    class Gamma implements GammaInterface {
        public function __construct(public string $value = 'gamma') {}
    }

    /** 3 fake providers
     */
    class AlphaProvider implements ServiceProviderInterface
    {
        public bool $registered = false;
        public bool $booted = false;
        public bool $terminated = false;

        public function __construct(
            public ContainerInterface $container)
        {}

        public function register(): void
        {
            $this->registered = true;
            $this->container->singleton(AlphaInterface::class, Alpha::class);
        }

        public function boot(): void
        { $this->booted = true; }

        public function terminate(): void
        { $this->terminated = true; }
    }

    class BetaProvider implements ServiceProviderInterface
    {
        public bool $registered = false;
        public bool $booted = false;
        public bool $terminated = false;

        public function __construct(
            public ContainerInterface $container)
        {}

        public function register(): void
        {
            $this->registered = true;
            $this->container->singleton(BetaInterface::class, Beta::class);
        }

        public function boot(): void
        { $this->booted = true; }

        public function terminate(): void
        { $this->terminated = true; }
    }

    class GammaProvider implements ServiceProviderInterface
    {
        public bool $registered = false;
        public bool $booted = false;
        public bool $terminated = false;

        public function __construct(
            public ContainerInterface $container)
        {}

        public function register(): void
        {
            $this->registered = true;
            $this->container->singleton(GammaInterface::class, Gamma::class);
        }

        public function boot(): void
        { $this->booted = true; }

        public function terminate(): void
        { $this->terminated = true; }
    }


    /** 31 Full application lifecycle
     */
    it('31 runs full application lifecycle with multiple providers', function () {
        $container = new Container();
        $manager = new TestableServiceManager($container);

        // register providers
        $manager->add(AlphaProvider::class);
        $manager->add(BetaProvider::class);
        $manager->add(GammaProvider::class);

        // lifecycle
        $manager->register();
        $manager->boot();

        // application runs (resolve services)
        $alpha = $container->get(AlphaInterface::class);
        $beta  = $container->get(BetaInterface::class);
        $gamma = $container->get(GammaInterface::class);

        // terminate
        $manager->terminate();

        // assertions
        expect($alpha)->toBeInstanceOf(Alpha::class)
            ->and($beta)->toBeInstanceOf(Beta::class)
            ->and($gamma)->toBeInstanceOf(Gamma::class);

        $collection = $manager->getCollection();

        // provider lifecycle assertions
        $providers = [
            AlphaProvider::class => $collection[AlphaProvider::class] ?? null,
            BetaProvider::class  => $collection[BetaProvider::class] ?? null,
            GammaProvider::class => $collection[GammaProvider::class] ?? null,
        ];

        foreach ($providers as $provider) {
            expect($provider->registered)->toBeTrue()
                ->and($provider->booted)->toBeTrue()
                ->and($provider->terminated)->toBeTrue();

            expect($provider->container)->toBe($container);
        }
    });
