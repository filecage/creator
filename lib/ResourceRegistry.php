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
         * @param object $instance
         * @param string $classResourceKey
         *
         * @return $this
         */
        function registerClassResource ($instance, $classResourceKey = null) {
            $classResourceKey = $classResourceKey ?: get_class($instance);
            $this->classResources[$classResourceKey] = $instance;

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

    }