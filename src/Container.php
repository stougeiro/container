<?php declare(strict_types=1);

    namespace STDW\Container;

    use STDW\Container\Contract\ContainerInterface;


    class Container implements ContainerInterface
    {
        protected static array $entries = [];

        protected static array $resolved_entries = [];

        protected static ContainerInterface $instance;


        public function __construct()
        {
            static::$instance = $this;

            $this->set(ContainerInterface::class, function() {
                return static::$instance;
            }, true);
        }


        public function has(string $abstract): bool
        {
            return array_key_exists($abstract, static::$entries);
        }

        public function get(string $abstract): mixed
        {
            if ( ! $this->has($abstract)) {
                $this->set($abstract, $abstract, false);
            }

            list($concrete, $shareable) = static::$entries[$abstract];

            if (array_key_exists($abstract, static::$resolved_entries) && $shareable) {
                return static::$resolved_entries[$abstract];
            }

            if (is_callable($concrete)) {
                $instance = $concrete($this);
            } else {
                $instance = $this->resolve($concrete);
            }

            if ($shareable) {
                static::$resolved_entries[$abstract] = $instance;
            }

            return $instance;
        }

        public function set(string $abstract, callable|string|null $concrete = null, bool $shareable = false): void
        {
            if ($this->has($abstract)) {
                throw ContainerException::classAlreadyExists($abstract);
            }

            if (is_null($concrete)) {
                $concrete = $abstract;
            }

            static::$entries[$abstract] = [$concrete, $shareable];
        }


        protected function resolve(string $abstract)
        {
            try {
                $reflectionClass = new \ReflectionClass($abstract);
            }
            catch(\ReflectionException $e) {
                throw ContainerException::classNotFound($abstract);
            }

            if ( ! $reflectionClass->isInstantiable()) {
                throw ContainerException::classNotInstanciable($abstract);
            }

            $constructor = $reflectionClass->getConstructor();

            if ( ! $constructor) {
                return new $abstract;
            }

            $parameters = $constructor->getParameters();

            if ( ! $parameters) {
                return new $abstract;
            }

            $dependencies = array_map
            (
                function (\ReflectionParameter $param) use ($abstract)
                {
                    if ($param->isDefaultValueAvailable()) {
                        return $param->getDefaultValue();
                    }

                    $name = $param->getName();
                    $type = $param->getType();

                    if ( ! $type) {
                        throw ContainerException::classDependencyNotResolved(
                            'Failed to resolve class "'. $abstract .'" because param "'. $name .'" is missing a type hint'
                        );
                    }

                    if ($type instanceof \ReflectionUnionType) {
                        throw ContainerException::classDependencyNotResolved(
                            'Failed to resolve class "'. $abstract .'" because of union type for param "'. $name .'"'
                        );
                    }

                    if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                        return $this->get($type->getName());
                    }

                    throw ContainerException::classDependencyNotResolved(
                        'Failed to resolve class "'. $abstract .'" because invalid param "'. $name .'"'
                    );
                },

                $parameters
            );

            return $reflectionClass->newInstanceArgs($dependencies);
        }
    }