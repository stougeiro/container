<?php

    use STDW\Container\Container;
    use STDW\Contract\Container\ContainerInterface;


    it('returns the same instance every time', function () {
        $c1 = container();
        $c2 = container();

        expect($c1)->toBe($c2);
    });

    it('lazy loads the container only once', function () {
        $first = container();
        expect($first)->toBeInstanceOf(Container::class);

        $second = container();
        expect($second)->toBe($first);
    });

    it('new Container() creates an independent instance', function () {
        $global = container();
        $local  = new Container();

        expect($local)->not->toBe($global);
    });

    it('container returns self when requested as interface', function () {
        $container = container();
        $other = $container->get(ContainerInterface::class);

        expect($container)->toBe($other);
        expect($container)->toBeInstanceOf(Container::class);
        expect($other)->toBeInstanceOf(Container::class);
        expect($container)->toBeInstanceOf(ContainerInterface::class);
        expect($other)->toBeInstanceOf(ContainerInterface::class);
    });
