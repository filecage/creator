<?php

    namespace Creator\Tests\Mocks;

    class MoreExtendedClassWithSingleDepedency {

        /**
         * @var ExtendedClass
         */
        private $extendedClass;

        /**
         * @param ExtendedClass $extendedClass
         */
        function __construct (ExtendedClass $extendedClass) {
            $this->extendedClass = $extendedClass;
        }

        /**
         * @return ExtendedClass
         */
        function getExtendedClass () : ExtendedClass {
            return $this->extendedClass;
        }

    }