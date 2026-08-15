<?php

    use STDW\Contract\Container\ContainerInterface;
    use STDW\Contract\Container\ServiceProviderAbstracted;
    use STDW\Container\Container;
    use Tests\Support\TestableServiceManager;


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

    /** TestableProviderAbstracted
     */
    abstract class TestableProviderAbstracted extends ServiceProviderAbstracted
    {
        public function getContainer(): ContainerInterface
        { return $this->container; }
    }

    /** 3 fake providers
     */
    class AlphaProvider extends TestableProviderAbstracted
    {
        public bool $registered = false;
        public bool $booted = false;
        public bool $terminated = false;

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

    class BetaProvider extends TestableProviderAbstracted
    {
        public bool $registered = false;
        public bool $booted = false;
        public bool $terminated = false;

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

    class GammaProvider extends TestableProviderAbstracted
    {
        public bool $registered = false;
        public bool $booted = false;
        public bool $terminated = false;

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
        $manager = new TestableServiceManager();

        // register providers
        $manager->add(new AlphaProvider($container));
        $manager->add(new BetaProvider($container));
        $manager->add(new GammaProvider($container));

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

        foreach ($collection as $provider) {
            expect($provider->registered)->toBeTrue()
                ->and($provider->booted)->toBeTrue()
                ->and($provider->terminated)->toBeTrue();

            expect($provider->getContainer())->toBe($container);
        }
    });
