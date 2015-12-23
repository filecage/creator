<?php

    namespace Creator\Tests\Mocks;

    use Creator\Interfaces\Factory;

    class SimpleAbstractClassFactory implements Factory {

        /**
         * @return SimpleClass
         */
        function createInstance () {
            return new SimpleClass();
        }

    }