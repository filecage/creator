<?php

    namespace Creator\Tests\Mocks;

    class InvalidFactoryTestUninstantiableClassFactory {
        function createInstance () {
            return new SimpleClass();
        }
    }