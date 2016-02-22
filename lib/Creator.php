<?php

    namespace Creator;

    use Creator\Exceptions\Unresolvable;

    class Creator {

        /**
         * @var ResourceRegistry
         */
        private $resourceRegistry;

        /**
         * @param ResourceRegistry $resourceRegistry
         */
        function __construct (ResourceRegistry $resourceRegistry = null) {
            $this->resourceRegistry = ($resourceRegistry) ?: new ResourceRegistry();
        }

        /**
         * @param string $className
         * @param bool $forceInstance Whether the class will be loaded from (or stored to) registry
         *
         * @throws \Exception
         * @throws Unresolvable
         * @return object
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
         * @param object $instance
         * @param string $classResourceKey
         *
         * @return $this
         */
        function registerClassResource ($instance, $classResourceKey = null) {
            $this->resourceRegistry->registerClassResource($instance, $classResourceKey);

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
         * @return int
         */
        function getCacheCount () {
            return $this->resourceRegistry->getRegisteredClassResourcesCount();
        }

    }