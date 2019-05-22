<?php

    namespace Creator;

    use Creator\Exceptions\InvalidFactory;
    use Creator\Exceptions\Unresolvable;
    use Creator\Interfaces\Factory;
    use ReflectionException;
    use ReflectionParameter;

    class Invocation {

        /**
         * @var Invokable
         */
        private $invokable;

        /**
         * @var ResourceRegistry
         */
        protected $resourceRegistry;

        /**
         * @var ResourceRegistry
         */
        protected $injectionRegistry;

        /**
         * @param Invokable $invokable
         * @param ResourceRegistry $resourceRegistry
         * @param ResourceRegistry|null $injections
         */
        function __construct (Invokable $invokable, ResourceRegistry $resourceRegistry, ResourceRegistry $injections = null) {
            $this->invokable = $invokable;
            $this->resourceRegistry = $resourceRegistry;
            $this->injectionRegistry = $injections ?: new ResourceRegistry();
        }

        /**
         * @return mixed
         */
        function invoke () {
            return $this->invokable->invoke($this->resolveDependencies());
        }

        /**
         * @param mixed $injected
         * @param string ...$resourceKeys
         *
         * @return $this
         */
        function with ($injected, ...$resourceKeys) {
            if (is_object($injected)) {
                $this->injectionRegistry->registerClassResource($injected, ...$resourceKeys);
            } else {
                foreach ($resourceKeys as $resourceKey) {
                    $this->injectionRegistry->registerPrimitiveResource($resourceKey, $injected);
                }
            }

            return $this;
        }

        /**
         * @param Factory|callable $factory
         * @param string $resourceKey
         *
         * @return $this
         * @throws InvalidFactory
         */
        function withFactory ($factory, string $resourceKey) {
            try {
                $invokable = InvokableFactory::createFromAnyFactory($factory);
                $this->injectionRegistry->registerFactoryForClassResource($resourceKey, $invokable);
            } catch (InvalidFactory $e) {
                throw $e->enrichClass($resourceKey);
            }

            return $this;
        }

        /**
         * @return array
         */
        private function resolveDependencies () {
            $resolvedDependencies = [];
            $dependencies = $this->invokable->getDependencies();
            foreach ($dependencies->getDependencies() as $dependency) {
                $resolvedDependencies[] = $this->resolveDependency($dependency);
            }

            return $resolvedDependencies;
        }

        /**
         * @param Dependency $dependency
         *
         * @return mixed|object
         * @throws Unresolvable
         * @throws ReflectionException
         */
        private function resolveDependency (Dependency $dependency) {
            if (!$dependency->isPrimitive()) {
                    return $this->getClassResource($dependency->getDependencyKey());
            }

            try {
                $primitiveResource = $this->getPrimitiveResource($dependency->getParameterName());

                return $primitiveResource;
            } catch (Unresolvable $e) {
                if ($dependency->isDefaultValueAvailable()) {
                    return $dependency->getDefaultValue();
                }
                throw $e;
            }
        }

        /**
         * @param string $classResourceKey
         *
         * @return object
         */
        private function getClassResource ($classResourceKey) {
            return (new Creation($classResourceKey, $this->resourceRegistry, $this->injectionRegistry))->create();
        }

        /**
         * @param string $resourceKey
         *
         * @return mixed
         * @throws Unresolvable
         */
        private function getPrimitiveResource ($resourceKey) {
            if ($this->injectionRegistry->hasPrimitiveResource($resourceKey)) {
                return $this->injectionRegistry->getPrimitiveResource($resourceKey);
            } elseif ($this->resourceRegistry->hasPrimitiveResource($resourceKey)) {
                return $this->resourceRegistry->getPrimitiveResource($resourceKey);
            }

            throw new UnresolvableDependency($resourceKey, null, $this->invokable->getName());
        }

    }