<?php declare(strict_types=1);

    namespace STDW\Container\Exception;

    use Exception;
    use Throwable;
    use Psr\Container\ContainerExceptionInterface;


    class ContainerException extends Exception implements ContainerExceptionInterface
    {
        public function __construct(string $message, ?Throwable $previous = null)
        {
            parent::__construct($message, previous: $previous);
        }
    }
