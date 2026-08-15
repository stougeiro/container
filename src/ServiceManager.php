<?php declare(strict_types=1);

    namespace STDW\Container;

    use InvalidArgumentException;
    use STDW\Contract\Container\ServiceManagerInterface;
    use STDW\Contract\Container\ServiceProviderInterface;


    class ServiceManager implements ServiceManagerInterface
    {
        /**
         * @var array<string, ServiceProviderInterface> */
        protected array $collection = [];


        public function __construct()
        { }


        /**
         * @param ServiceProviderInterface $provider 
         * @return void 
         * @throws InvalidArgumentException 
         */
        public function add(ServiceProviderInterface $provider): void
        {
            $fqcn = $provider::class;

            if (isset($this->collection[$fqcn])) {
                throw new InvalidArgumentException("Provider {$fqcn} is already registered");
            }

            $this->collection[$fqcn] = $provider;
        }

        public function register(): void
        {
            foreach ($this->collection as $provider) {
                $provider->register();
            }
        }

        public function boot(): void
        {
            foreach ($this->collection as $provider) {
                $provider->boot();
            }
        }

        public function terminate(): void
        {
            foreach ($this->collection as $provider) {
                $provider->terminate();
            }
        }
    }
