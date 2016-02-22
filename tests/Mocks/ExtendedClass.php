<?php

    namespace Creator\Tests\Mocks;

    class ExtendedClass implements ExtendedInterface {

        /**
         * @var SimpleClass
         */
        private $simpleClass;

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