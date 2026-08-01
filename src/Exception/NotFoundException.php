<?php declare(strict_types=1);

    namespace STDW\Container\Exception;

    use Exception;
    use Psr\Container\NotFoundExceptionInterface;


    class NotFoundException extends Exception implements NotFoundExceptionInterface
    {
        public function __construct(string $id)
        {
            parent::__construct("No entry was found for the identifier: {$id}");
        }
    }
