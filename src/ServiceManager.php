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


        /** @return void 
         */
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

        /** @return void 
         */
        public function register(): void
        {
            foreach ($this->collection as $provider) {
                $provider->register();
            }
        }

        /** @return void 
         */
        public function boot(): void
        {
            foreach ($this->collection as $provider) {
                $provider->boot();
            }
        }

        /** @return void 
         */
        public function terminate(): void
        {
            foreach ($this->collection as $provider) {
                $provider->terminate();
            }
        }
    }
