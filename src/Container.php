<?php declare(strict_types=1);

    namespace STDW\Container;

    use Closure;
    use Throwable;
    use STDW\Contract\Container\ContainerInterface;
    use STDW\Container\Exception\NotFoundException;
    use STDW\Container\Exception\ContainerException;
    use STDW\Contract\Container\ServiceFactoryInterface;


    class Container implements ContainerInterface
    {
        /** @var array<string, array{concrete: callable|string|null, shareable: bool}>
         */
        private array $entries = [];

        /** @var array<string, mixed>
         */
        private array $resolved = [];


        public function __construct()
        { }


        /**
         * @param string $id 
         * @return bool 
         */
        public function has(string $id): bool
        {
            return isset($this->entries[$id]);
        }

        /**
         * @param string $id 
         * @return mixed 
         * @throws NotFoundException 
         */
        public function get(string $id): mixed
        {
            if ( ! $this->has($id) ) {
                throw new NotFoundException($id);
            }

            $entry = $this->entries[$id];

            if ($entry['shareable']) {
                return $this->resolved[$id] ??= $this->build($entry['concrete']);
            }

            return $this->build($entry['concrete']);
        }

        /**
         * @param string $id 
         * @param callable|string|null $implementation 
         * @param bool $shareable 
         * @return void 
         * @throws ContainerException 
         */
        public function set(string $id, callable|string|null $implementation  = null, bool $shareable = false): void
        {
            if ($this->has($id) ) {
                throw new ContainerException("Entry already exists for identifier: {$id}");
            }

            $this->entries[$id] = [
                'concrete'  => $implementation ?? $id,
                'shareable' => $shareable,
            ];

            if ($shareable) {
                $this->resolved[$id] = null;
            }
        }

        /**
         * @param string $id 
         * @param callable|string|null $implementation 
         * @return void 
         */
        public function bind(string $id, callable|string|null $implementation  = null): void
        {
            $this->set($id, $implementation, false);
        }

        /**
         * @param string $id 
         * @param callable|string|null $implementation 
         * @return void 
         */
        public function singleton(string $id, callable|string|null $implementation  = null): void
        {
            $this->set($id, $implementation, true);
        }


        /**
         * Builds a concrete instance from the given implementation.
         * Supports closures (as factories), ServiceFactoryInterface-based classes and direct class instantiation.
         * 
         * @param callable|string|null $implementation 
         * @return mixed 
         * @throws ContainerException 
         */
        private function build(callable|string|null $implementation): mixed
        {
            if ($implementation instanceof Closure) {
                return $implementation($this);
            }

            if ( ! is_string($implementation)) {
                throw new ContainerException('Invalid concrete definition');
            }

            try
            {
                if (is_subclass_of($implementation, ServiceFactoryInterface::class)) {
                    return $implementation::factory($this);
                }

                return new $implementation;
            }
            catch (Throwable $e) {
                throw new ContainerException("Failed to instantiate service [$implementation]: {$e->getMessage()}", $e);
            }
        }
    }
