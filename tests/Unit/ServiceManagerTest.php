<?php

    use STDW\Container\Container;
    use STDW\Container\ServiceManager;
    use STDW\Contract\Container\ContainerInterface;
    use STDW\Contract\Container\ServiceProviderAbstracted;
    use Tests\Support\TestableServiceManager;


    class TestProvider extends ServiceProviderAbstracted
    {
        public bool $registered = false;
        public bool $booted = false;
        public bool $terminated = false;

        public function register(): void
        { $this->registered = true; }

        public function boot(): void
        { $this->booted = true; }

        public function terminate(): void
        { $this->terminated = true; }

        public function getContainer(): ContainerInterface
        { return $this->container; }
    }

    /** Without ServiceProviderInterface implementation */
    class InvalidProvider {}

    class BrokenProvider extends ServiceProviderAbstracted
    {
        public function register(): void
        { throw new RuntimeException("register failed"); }

        public function boot(): void
        { throw new RuntimeException("boot failed"); }

        public function terminate(): void
        { throw new RuntimeException("terminate failed"); }
    }


    it('provider verification when added', function () {
        $container = new Container();
        $manager = new TestableServiceManager();
        $manager->add(new TestProvider($container));

        $collection = $manager->getCollection();

        expect($collection[TestProvider::class])
            ->toBeInstanceOf(TestProvider::class);
    });

    it('provider receives the same container instance', function () {
        $container = new Container();
        $manager = new TestableServiceManager();
        $manager->add(new TestProvider($container));

        $collection = $manager->getCollection();
        $provider = $collection[TestProvider::class];

        expect($provider->getContainer())->toBe($container);
    });

    it('throws when adding the same provider twice', function () {
        $container = new Container();
        $manager = new TestableServiceManager();
        $testProvider = new TestProvider($container);
        $manager->add($testProvider);

        expect(fn() => $manager->add($testProvider))
            ->toThrow(InvalidArgumentException::class);
    });

    it('executes register(), boot() and terminate() lifecycle', function () {
        $container = new Container();
        $manager = new TestableServiceManager();
        $manager->add(new TestProvider($container));

        $collection = $manager->getCollection();
        $provider = $collection[TestProvider::class];

        $manager->register();
        $manager->boot();
        $manager->terminate();

        expect($provider->registered)->toBeTrue()
            ->and($provider->booted)->toBeTrue()
            ->and($provider->terminated)->toBeTrue();
    });

    it('provider is instantiated only once', function () {
        $container = new Container();
        $manager = new TestableServiceManager();
        $manager->add(new TestProvider($container));

        $collection = $manager->getCollection();

        $first = $collection[TestProvider::class];
        $second = $collection[TestProvider::class];

        expect($first)->toBe($second);
    });

    it('propagates provider exceptions during lifecycle', function () {
        $container = new Container();
        $manager = new ServiceManager();
        $manager->add(new BrokenProvider($container));

        expect(fn() => $manager->register())
            ->toThrow(RuntimeException::class);

        expect(fn() => $manager->boot())
            ->toThrow(RuntimeException::class);

        expect(fn() => $manager->terminate())
            ->toThrow(RuntimeException::class);
    });
