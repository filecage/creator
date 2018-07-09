<?php

    namespace Creator\Tests\Mocks;

    class ArbitraryClassOnlyResolvableByFactory {

        /**
         * @var SimpleClass
         */
        private $simpleClass;

        /**
         * @var string
         */
        private $primitiveValue;

        /**
         * @param SimpleClass $simpleClass
         * @param string $primitiveValue
         */
        function __construct (SimpleClass $simpleClass, string $primitiveValue) {
            $this->simpleClass = $simpleClass;
            $this->primitiveValue = $primitiveValue;
        }

        /**
         * @return SimpleClass
         */
        function getSimpleClass () {
            return $this->simpleClass;
        }

        /**
         * @return string
         */
        function getPrimitiveValue () {
            return $this->primitiveValue;
        }

    }