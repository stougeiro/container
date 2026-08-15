<?php declare(strict_types=1);

    use STDW\Contract\Container\ContainerInterface;
    use STDW\Container\Container;


    /** @return ContainerInterface 
     */
    function container(): ContainerInterface
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new Container();
            $instance->singleton(ContainerInterface::class, function() use ($instance) {
                return $instance;
            });
        }

        return $instance;
    }
