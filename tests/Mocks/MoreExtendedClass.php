<?php

    namespace Creator\Tests\Mocks;

    class MoreExtendedClass {

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
         * @return ExtendedClass
         */
        function getExtendedClass () {
            return $this->extendedClass;
        }

        /**
         * @return SimpleClass
         */
        function getSimpleClass () {
            return $this->simpleClass;
        }

        /**
         * @return AnotherSimpleClass
         */
        function getAnotherSimpleClass () {
            return $this->anotherSimpleClass;
        }

    }