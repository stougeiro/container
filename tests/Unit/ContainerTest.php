<?php

    use STDW\Contract\Container\ContainerInterface;
    use STDW\Contract\Container\ServiceFactoryInterface;

    use STDW\Container\Container;
    use STDW\Container\Exception\NotFoundException;
    use STDW\Container\Exception\ContainerException;


    interface FooInterface
    { }

    class Foo implements FooInterface
    {
        public function __construct(public string $value = '') {}
    }

    class Bar implements FooInterface, ServiceFactoryInterface
    {
        public function __construct(public string $value = '') {}

        public static function factory(ContainerInterface $container): static
        {
            return new Bar('factory');
        }
    }

    class BrokenFactory implements ServiceFactoryInterface
    {
        public static function factory(ContainerInterface $container): static
        {
            throw new RuntimeException('boom');
        }
    }

    class BrokenImplementation implements FooInterface
    {
        public function __construct(public string $value) {}
    }


    /** 11 - set()
     */
    it('11 registers a service using set()', function () {
        $container = new Container();
        $container->set(FooInterface::class, Foo::class);

        expect($container->get(FooInterface::class))->toBeInstanceOf(Foo::class);
    });

    /** 12 - (set) bind()
     */
    it('12 bind() creates a new instance every time', function () {
        $container = new Container();
        $container->bind(FooInterface::class, Foo::class);

        $a = $container->get(FooInterface::class);
        $b = $container->get(FooInterface::class);

        expect($a)->not->toBe($b);
    });

    /** 13 - (set) singleton()
     */
    it('13 singleton() returns the same instance', function () {
        $container = new Container();
        $container->singleton(FooInterface::class, Foo::class);

        $a = $container->get(FooInterface::class);
        $b = $container->get(FooInterface::class);

        expect($a)->toBe($b);
    });

    /** 14 - factory()
     */
    it('14 resolves services using a factory class', function () {
        $container = new Container();
        $container->singleton(FooInterface::class, Bar::class);

        $bar = $container->get(FooInterface::class);

        expect($bar)->toBeInstanceOf(Bar::class)
                    ->and($bar->value)->toBe('factory');
    });

    /** 15 - interface bound to implementation
     */
    it('15 resolves interface when explicitly bound to implementation', function () {
        $container = new Container();
        $container->singleton(FooInterface::class, Foo::class);

        $foo = $container->get(FooInterface::class);

        expect($foo)->toBeInstanceOf(Foo::class);
    });

    /** 16 - NotFoundException
     */
    it('16 throws NotFoundException when service is missing', function () {
        $container = new Container();

        expect(fn() => $container->get('missing'))
            ->toThrow(NotFoundException::class);
    });

    /** 17 - interface without binding → NotFoundException
     */
    it('17 throws NotFoundException when resolving interface without binding', function () {
        $container = new Container();

        expect(fn() => $container->get(FooInterface::class))
            ->toThrow(NotFoundException::class);
    });

    /** 18 - BrokenFactory → ContainerException
     */
    it('18 wraps factory errors inside ContainerException', function () {
        $container = new Container();
        $container->singleton(FooInterface::class, BrokenFactory::class);

        expect(fn() => $container->get(FooInterface::class))
            ->toThrow(ContainerException::class);
    });

    /** 19 - BrokenImplementation → ContainerException
     */
    it('19 wraps direct implementation errors inside ContainerException', function () {
        $container = new Container();
        $container->singleton(BrokenImplementation::class);

        expect(fn() => $container->get(BrokenImplementation::class))
            ->toThrow(ContainerException::class);
    });
