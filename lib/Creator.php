<?php

    namespace Creator;

    use Creator\Exceptions\InvalidFactory;
    use Creator\Exceptions\Unresolvable;
    use Creator\Interfaces\Factory;

    class Creator extends AbstractResourceRegistryAware {

        /**
         * @param ResourceRegistry $resourceRegistry
         */
        function __construct (ResourceRegistry $resourceRegistry = null) {
            parent::__construct(($resourceRegistry) ?: new ResourceRegistry());
        }

        /**
         * @template className
         * @param class-string<className> $className
         * @param bool $forceInstance Whether the class will be loaded from (or stored to) registry
         *
         * @throws Unresolvable
         * @return className
         */
        function create ($className, $forceInstance = false) {
            return (new Creation($className, ($forceInstance) ? $this->resourceRegistry->cloneWithout($className) : $this->resourceRegistry))->create();
        }

        /**
         * @param string $className
         * @param bool $forceInstance
         *
         * @throws Unresolvable
         * @return Creation
         */
        function createInjected ($className, $forceInstance = false) {
            return new Creation($className, ($forceInstance) ? $this->resourceRegistry->cloneWithout($className) : $this->resourceRegistry);
        }

        /**
         * @param callable $callable
         *
         * @return mixed
         */
        function invoke (callable $callable) {
            return (new Invocation(InvokableClosure::createFromCallable($callable), $this->resourceRegistry))->invoke();
        }

        /**
         * @param callable $callable
         *
         * @return Invocation
         */
        function invokeInjected (callable $callable) {
            return new Invocation(InvokableClosure::createFromCallable($callable), $this->resourceRegistry);
        }

        /**
         * @param object $instance
         * @param string ...$classResourceKey
         *
         * @return $this
         */
        function registerClassResource ($instance, ...$classResourceKey) {
            $this->resourceRegistry->registerClassResource($instance, ...$classResourceKey);

            return $this;
        }

        /**
         * @param string $resourceKey
         * @param mixed $value
         *
         * @return $this
         */
        function registerPrimitiveResource ($resourceKey, $value) {
            $this->resourceRegistry->registerPrimitiveResource($resourceKey, $value);

            return $this;
        }

        /**
         * @param Factory|callable $factory Factory class name, Factory instance or Factory closure
         * @param string $classResourceKey
         *
         * @return $this
         * @throws InvalidFactory
         */
        function registerFactory($factory, string $classResourceKey) {
            try {
                $invokable = InvokableFactory::createFromAnyFactory($factory);
                $this->resourceRegistry->registerFactoryForClassResource($classResourceKey, $invokable);
            } catch (InvalidFactory $e) {
                throw $e->enrichClass($classResourceKey);
            }

            return $this;
        }

        /**
         * @return int
         */
        function getCacheCount () {
            return $this->resourceRegistry->getRegisteredClassResourcesCount();
        }

    }