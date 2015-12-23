<?php

    namespace Creator\Tests\Mocks;

    use Creator\Interfaces\Factory;

    class SimpleInterfaceFactory implements Factory {

        function createInstance () {
            return new SimpleClass();
        }

    }