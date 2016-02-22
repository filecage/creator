<?php

    namespace Creator\Tests\Mocks;

    use Creator\Interfaces\Factory;

    class ExtendedInterfaceFactory implements Factory {

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
         * @return ExtendedClass
         */
        function createInstance () {
            return new ExtendedClass($this->simpleClass);
        }

    }