<?php

    namespace Creator\Tests\Mocks;

    use Creator\Interfaces\Factory;

    class InvalidFactoryInstanceTestUninstantiableClassFactory implements Factory {
        function createInstance () {
            return new SimpleClass();
        }
    }