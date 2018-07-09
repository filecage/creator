<?php

    namespace Creator\Tests\Mocks;

    use Creator\Interfaces\Factory;

    class ArbitraryFactoryWithStringValue implements Factory {

        /**
         * @var SimpleClass
         */
        private $simpleClass;

        /**
         * @var ArbitraryClassWithStringValue
         */
        private $arbitraryClassWithStringValue;

        /**
         * @param SimpleClass $simpleClass
         * @param ArbitraryClassWithStringValue $arbitraryClassWithStringValue
         * @internal param string $primitiveValue
         */
        function __construct (SimpleClass $simpleClass, ArbitraryClassWithStringValue $arbitraryClassWithStringValue) {
            $this->simpleClass = $simpleClass;
            $this->arbitraryClassWithStringValue = $arbitraryClassWithStringValue;
        }

        /**
         * @return ArbitraryClassOnlyResolvableByFactory
         */
        function createInstance () {
            return new ArbitraryClassOnlyResolvableByFactory($this->simpleClass, $this->arbitraryClassWithStringValue->getStringValue());
        }

    }