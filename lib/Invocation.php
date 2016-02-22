<?php

    namespace Creator;

    use Creator\Exceptions\Unresolvable;
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
         * @param object $instance
         * @param null $classResourceKey
         *
         * @return $this
         */
        function with ($instance, $classResourceKey = null) {
            $this->injectionRegistry->registerClassResource($instance, $classResourceKey);

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
         * @param ReflectionParameter $dependency
         *
         * @return mixed|object
         * @throws Unresolvable
         * @throws ReflectionException
         */
        private function resolveDependency (ReflectionParameter $dependency) {
            $class = $dependency->getClass();
            if ($class) {
                return $this->getClassResource($class->getName());
            }

            try {
                $primitiveResource = $this->getPrimitiveResource($dependency->getName());

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
            $instance = $this->injectionRegistry->getClassResource($classResourceKey) ?: $this->resourceRegistry->getClassResource($classResourceKey);

            if (!$instance) {
                $instance = (new Creation($classResourceKey, $this->resourceRegistry, $this->injectionRegistry))->create();
            }

            return $instance;
        }

        /**
         * @param string $resourceKey
         *
         * @return mixed
         * @throws Unresolvable
         */
        private function getPrimitiveResource ($resourceKey) {
            return $this->resourceRegistry->getPrimitiveResource($resourceKey);
        }

    }