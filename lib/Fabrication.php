<?php

    namespace Creator;

    class Fabrication extends Invocation {

        /**
         * @var Invokable
         */
        private $invokable;
        /**
         * @var ResourceRegistry
         */
        private $mainRegistry;

        /**
         * @param Invokable $invokable
         * @param ResourceRegistry $resourceRegistry
         * @param ResourceRegistry|null $injections
         * @param ResourceRegistry|null $mainRegistry
         */
        function __construct (Invokable $invokable, ResourceRegistry $resourceRegistry, ResourceRegistry $injections = null, ResourceRegistry $mainRegistry = null) {
            parent::__construct($invokable, $resourceRegistry, $injections);
            $this->invokable = $invokable;
            $this->mainRegistry = $mainRegistry ?? $resourceRegistry;
        }

        /**
         * @return object|null
         */
        function fabricate () {
            $instance = $this->invokable;
            $isInjectedInstance = false;

            while ($instance instanceof Invokable) {
                $isInjectedInstance = $isInjectedInstance ?: $this->injectionRegistry->containsAnyOf($instance->getDependencies());
                $invocation = new Invocation($instance, $this->resourceRegistry, $this->injectionRegistry);
                $instance = $invocation->invoke();
            }

            if ($instance !== null) {
                $registry = $isInjectedInstance ? $this->injectionRegistry : $this->mainRegistry;
                $registry->registerClassResource($instance);
            }

            return $instance;
        }

    }