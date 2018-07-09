<?php

    namespace Creator\Tests\Mocks;

    use Creator\Interfaces\Factory;

    class ArbitraryFactory implements Factory {

        const PRIMITIVE_VALUE = 'PRIMITIVE_VALUE';
        const ANOTHER_PRIMITIVE_VALUE = 'ANOTHER_PRIMITIVE_VALUE';
        const YET_ANOTHER_PRIMITIVE_VALUE = 'YET_ANOTHER_PRIMITIVE_VALUE';
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
        function __construct (SimpleClass $simpleClass, string $primitiveValue = self::PRIMITIVE_VALUE) {
            $this->simpleClass = $simpleClass;
            $this->primitiveValue = $primitiveValue;
        }

        /**
         * @return ArbitraryClassOnlyResolvableByFactory
         */
        function createInstance () {
            return new ArbitraryClassOnlyResolvableByFactory($this->simpleClass, $this->primitiveValue);
        }

    }