<?php declare(strict_types=1);

    use STDW\Contract\Container\ContainerInterface;
    use STDW\Container\Container;


    /** @return ContainerInterface 
     */
    function container(): ContainerInterface
    {
        static $instance;

        return $instance ??= new Container();
    }
