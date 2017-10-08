<?php

    namespace Creator;

    class LayerableResourceRegistry extends ResourceRegistry {


        /**
         * @var ResourceRegistry
         */
        private $innerRegistry;

        /**
         * @var ResourceRegistry
         */
        private $outerRegistry;

        /**
         * @param ResourceRegistry $innerRegistry
         * @param ResourceRegistry $outerRegistry
         */
        function __construct (ResourceRegistry $innerRegistry, ResourceRegistry $outerRegistry = null) {
            $this->innerRegistry = $innerRegistry;
            $this->outerRegistry = $outerRegistry;
        }

        /**
         * @return LayerableResourceRegistry
         */
        function getOuterRegistry () {
            if (!isset($this->outerRegistry)) {
                $this->outerRegistry = new LayerableResourceRegistry($this);
            }

            return $this->outerRegistry;
        }

        /**
         * @param string $classResourceKey
         * @return object
         */
        function getClassResource ($classResourceKey) {
            return parent::getClassResource($classResourceKey) ?? $this->innerRegistry->getClassResource($classResourceKey);
        }

        /**
         * @param string $resourceKey
         * @return mixed
         */
        function getPrimitiveResource ($resourceKey) {
            return parent::getPrimitiveResource($resourceKey) ?? $this->innerRegistry->getPrimitiveResource($resourceKey);
        }

        /**
         * @param Creatable $creatable
         * @return object
         */
        function findFulfillingInstance (Creatable $creatable) {
            return parent::findFulfillingInstance($creatable) ?? $this->innerRegistry->findFulfillingInstance($creatable);
        }

        /**
         * @return int
         */
        function getRegisteredClassResourcesCount () {
            return parent::getRegisteredClassResourcesCount() + $this->innerRegistry->getRegisteredClassResourcesCount();
        }

        /**
         * @param DependencyContainer $dependencyContainer
         * @return bool
         */
        function containsAnyOf (DependencyContainer $dependencyContainer) {
            return parent::containsAnyOf($dependencyContainer) || $this->innerRegistry->containsAnyOf($dependencyContainer);
        }

        /**
         * @param string $exceptedClass
         * @return LayerableResourceRegistry
         */
        function cloneWithout ($exceptedClass) {
            /** @var LayerableResourceRegistry $clone */
            $clone = parent::cloneWithout($exceptedClass);
            $clone->innerRegistry = $this->innerRegistry->cloneWithout($exceptedClass);

            return $clone;
        }

    }