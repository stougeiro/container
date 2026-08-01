<?php declare(strict_types=1);

    namespace STDW\Container;

    use InvalidArgumentException;
    use STDW\Contract\Container\ServiceManagerInterface;
    use STDW\Contract\Container\ContainerInterface;
    use STDW\Contract\Container\ServiceProviderInterface;


    class ServiceManager implements ServiceManagerInterface
    {
        /**
         * @var array<string, ServiceProviderInterface> */
        protected array $collection = [];


        public function __construct(
            protected ContainerInterface $container)
        { }


        /**
         * @param string $provider 
         * @return void 
         * @throws InvalidArgumentException 
         */
        public function add(string $provider): void
        {
            if ( ! is_subclass_of($provider, ServiceProviderInterface::class)) {
                throw new InvalidArgumentException("Provider {$provider} must implement ServiceProviderInterface");
            }

            if (isset($this->collection[$provider])) {
                throw new InvalidArgumentException("Provider {$provider} is already registered");
            }

            $this->collection[$provider] = new $provider($this->container);
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
