<?php

    namespace Creator\Tests\Mocks;

    class MoreExtendedClassWithExtendedAndSimpleDependency {

        /**
         * @var ExtendedClass
         */
        private $extendedClass;

        /**
         * @var AnotherSimpleClass
         */
        private $anotherSimpleClass;

        /**
         * @param ExtendedClass $extendedClass
         * @param AnotherSimpleClass $anotherSimpleClass
         */
        function __construct (ExtendedClass $extendedClass, AnotherSimpleClass $anotherSimpleClass) {
            $this->extendedClass = $extendedClass;
            $this->anotherSimpleClass = $anotherSimpleClass;
        }

        /**
         * @return ExtendedClass
         */
        function getExtendedClass () : ExtendedClass {
            return $this->extendedClass;
        }

        /**
         * @return AnotherSimpleClass
         */
        function getAnotherSimpleClass () : AnotherSimpleClass {
            return $this->anotherSimpleClass;
        }

    }