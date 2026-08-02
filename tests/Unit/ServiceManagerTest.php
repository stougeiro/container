<?php

    use STDW\Container\Container;
    use STDW\Container\ServiceManager;
    use STDW\Contract\Container\ContainerInterface;
    use STDW\Contract\Container\ServiceProviderInterface;


    // class TestableServiceManager extends ServiceManager
    // {
    //     public function getContainer(): ContainerInterface
    //     { return $this->container; }

    //     public function getCollection(): array
    //     { return $this->collection; }
    // }

    class TestProvider implements ServiceProviderInterface
    {
        public bool $registered = false;
        public bool $booted = false;
        public bool $terminated = false;

        public function __construct(
            public ContainerInterface $container)
        { }

        public function register(): void
        { $this->registered = true; }

        public function boot(): void
        { $this->booted = true; }

        public function terminate(): void
        { $this->terminated = true; }
    }

    /**
     * Without ServiceProviderInterface implementation */
    class InvalidProvider {}

    class BrokenProvider implements ServiceProviderInterface
    {
        public function __construct(
            protected ContainerInterface $container)
        { }

        public function register(): void
        { throw new RuntimeException("register failed"); }

        public function boot(): void
        { throw new RuntimeException("boot failed"); }

        public function terminate(): void
        { throw new RuntimeException("terminate failed"); }
    }


    /** 21 — provider added correctly
     */
    it('21 provider verification when added', function () {
        $manager = new TestableServiceManager(new Container());
        $manager->add(TestProvider::class);

        $collection = $manager->getCollection();

        expect($collection[TestProvider::class])
            ->toBeInstanceOf(TestProvider::class);
    });

    /** 22 — provider recieves same container instance
     */
    it('22 provider receives the same container instance', function () {
        $container = new Container();

        $manager = new TestableServiceManager($container);
        $manager->add(TestProvider::class);

        $collection = $manager->getCollection();
        $provider = $collection[TestProvider::class];

        expect($provider->container)->toBe($container);
    });

    /** 23 — invalid provider throws exception
     */
    it('23 throws when provider does not implement ServiceProviderInterface', function () {
        $manager = new ServiceManager(new Container());

        expect(fn() => $manager->add(InvalidProvider::class))
            ->toThrow(InvalidArgumentException::class);
    });

    /** 24 — adding the same provider twice throws exception
     */
    it('24 throws when adding the same provider twice', function () {
        $manager = new ServiceManager(new Container());
        $manager->add(TestProvider::class);

        expect(fn() => $manager->add(TestProvider::class))
            ->toThrow(InvalidArgumentException::class);
    });

    /** 25 — provider lifecycle methods are executed correctly
     */
    it('25 executes register(), boot() and terminate() lifecycle', function () {
        $manager = new TestableServiceManager(new Container());
        $manager->add(TestProvider::class);

        $collection = $manager->getCollection();
        $provider = $collection[TestProvider::class];

        $manager->register();
        $manager->boot();
        $manager->terminate();

        expect($provider->registered)->toBeTrue()
            ->and($provider->booted)->toBeTrue()
            ->and($provider->terminated)->toBeTrue();
    });

    /** 26 — provider is instantiated only once
     */
    it('26 provider is instantiated only once', function () {
        $manager = new TestableServiceManager(new Container());
        $manager->add(TestProvider::class);

        $collection = $manager->getCollection();

        $first = $collection[TestProvider::class];
        $second = $collection[TestProvider::class];

        expect($first)->toBe($second);
    });

    /** 7 — propagates provider exceptions during lifecycle
     */
    it('27 propagates provider exceptions during lifecycle', function () {
        $manager = new ServiceManager(new Container());
        $manager->add(BrokenProvider::class);

        expect(fn() => $manager->register())
            ->toThrow(RuntimeException::class);

        expect(fn() => $manager->boot())
            ->toThrow(RuntimeException::class);

        expect(fn() => $manager->terminate())
            ->toThrow(RuntimeException::class);
    });
