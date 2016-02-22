<?php

    namespace Creator;

    use Creator\Exceptions\Unresolvable;

    class ResourceRegistry {

        /**
         * @var array
         */
        private $classResources = [];

        /**
         * @var array
         */
        private $primitiveResources = [];

        /**
         * @var callable
         */
        private $onRegistration;

        /**
         * @param object $instance
         * @param string $classResourceKey
         *
         * @return $this
         */
        function registerClassResource ($instance, $classResourceKey = null) {
            $classResourceKey = $classResourceKey ?: get_class($instance);
            $this->classResources[$classResourceKey] = $instance;

            if ($this->onRegistration) {
                call_user_func($this->onRegistration, $instance, $classResourceKey);
            }

            return $this;
        }

        /**
         * @param string $classResourceKey
         *
         * @return object
         */
        function getClassResource ($classResourceKey) {
            if (!isset($this->classResources[$classResourceKey])) {
                return null;
            }

            return $this->classResources[$classResourceKey];
        }

        /**
         * @return int
         */
        function getRegisteredClassResourcesCount () {
            return count($this->classResources);
        }

        /**
         * @param string $resourceKey
         * @param mixed $value
         *
         * @return $this
         */
        function registerPrimitiveResource ($resourceKey, $value) {
            $this->primitiveResources[$resourceKey] = $value;

            return $this;
        }

        /**
         * @param string $resourceKey
         *
         * @return mixed
         * @throws Unresolvable
         */
        function getPrimitiveResource ($resourceKey) {
            if (!isset($this->primitiveResources[$resourceKey])) {
                throw new Unresolvable('Tried to load dependency "' . $resourceKey . '" with unknown primitive resource');
            }

            return $this->primitiveResources[$resourceKey];
        }

        /**
         * @param DependencyContainer $dependencyContainer
         *
         * @return bool
         */
        function containsAnyOf (DependencyContainer $dependencyContainer) {
            foreach ($dependencyContainer->getDependencies() as $dependency) {
                $class = $dependency->getClass();
                if ($class && $this->getClassResource($class->getName())) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param string $exceptedClass
         *
         * @return ResourceRegistry
         */
        function cloneWithout ($exceptedClass) {
            $clone = clone $this;
            unset($clone->classResources[$exceptedClass]);
            $clone->onRegistration(function($instance, $class) use ($exceptedClass){
                if ($class !== $exceptedClass) {
                    $this->registerClassResource($instance, $class);
                }
            });

            return $clone;
        }

        /**
         * @param callable $callback
         *
         * @return $this
         */
        private function onRegistration (callable $callback) {
            $this->onRegistration = $callback;

            return $this;
        }

    }