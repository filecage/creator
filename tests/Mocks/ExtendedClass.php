<?php

    namespace Creator\Tests\Mocks;

    class ExtendedClass {

        /**
         * @param SimpleClass $simpleClass
         */
        function __construct(SimpleClass $simpleClass) {
            $this->simpleClass = $simpleClass;
        }

        /**
         * @return SimpleClass
         */
        function getSimpleClass () {
            return $this->simpleClass;
        }
    }