<?php

    use STDW\Container\Container;


    it('41 returns the same instance every time', function () {
        $c1 = container();
        $c2 = container();

        expect($c1)->toBe($c2);
    });

    it('42 lazy loads the container only once', function () {
        $first = container();
        expect($first)->toBeInstanceOf(Container::class);

        $second = container();
        expect($second)->toBe($first);
    });

    it('43 new Container() creates an independent instance', function () {
        $global = container();
        $local  = new Container();

        expect($local)->not->toBe($global);
    });
