<?php declare(strict_types=1);

    namespace STDW\Container;

    use Psr\Container\ContainerExceptionInterface;


    class ContainerException extends \Exception implements ContainerExceptionInterface
    {
        public static function classAlreadyExists(string $class): object
        {
            return new static('Class "'. $class .'" already exists');
        }

        public static function classNotFound(string $class): object
        {
            return new static('Class "'. $class .'" not found');
        }

        public static function classNotInstanciable(string $class): object
        {
            return new static('Class "'. $class .'" is not instantiable');
        }

        public static function classDependencyNotResolved(string $message): object
        {
            return new static($message);
        }
    }