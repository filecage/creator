<?php

    namespace Creator\Tests\Mocks;

    use Creator\Interfaces\Factory;

    class MoreExtendedInterfaceFactory implements Factory {

        /**
         * @var ExtendedClass
         */
        private $extendedClass;
        /**
         * @var SimpleClass
         */
        private $simpleClass;
        /**
         * @var AnotherSimpleClass
         */
        private $anotherSimpleClass;

        /**
         * @param ExtendedClass $extendedClass
         * @param SimpleClass $simpleClass
         * @param AnotherSimpleClass $anotherSimpleClass
         */
        function __construct(ExtendedClass $extendedClass, SimpleClass $simpleClass, AnotherSimpleClass $anotherSimpleClass) {
            $this->extendedClass = $extendedClass;
            $this->simpleClass = $simpleClass;
            $this->anotherSimpleClass = $anotherSimpleClass;
        }

        /**
         * @return MoreExtendedClass
         */
        function createInstance () {
            return new MoreExtendedClass($this->extendedClass, $this->simpleClass, $this->anotherSimpleClass);
        }

    }