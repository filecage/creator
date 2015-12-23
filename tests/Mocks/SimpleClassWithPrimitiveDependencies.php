<?php

    namespace Creator\Tests\Mocks;

    class SimpleClassWithPrimitiveDependencies {

        const FROM_REGISTRY = 'fromRegistry';
        const FROM_DEFAULT = 'fromDefault';

        /**
         * @var string
         */
        private $fromRegistry;

        /**
         * @var string
         */
        private $fromRegistryWithDefault;

        /**
         * @var string
         */
        private $fromDefault;

        /**
         * @param string $fromRegistry
         * @param string $fromRegistryWithDefault
         * @param string $fromDefault
         */
        function __construct($fromRegistry, $fromRegistryWithDefault = self::FROM_DEFAULT, $fromDefault = self::FROM_DEFAULT) {
            $this->fromRegistry = $fromRegistry;
            $this->fromDefault = $fromDefault;
            $this->fromRegistryWithDefault = $fromRegistryWithDefault;
        }

        /**
         * @return string
         */
        function getFromRegistry () {
            return $this->fromRegistry;
        }

        /**
         * @return string
         */
        function getFromRegistryWithDefault () {
            return $this->fromRegistryWithDefault;
        }

        /**
         * @return string
         */
        function getFromDefault () {
            return $this->fromDefault;
        }

    }