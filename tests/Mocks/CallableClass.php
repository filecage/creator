<?php

    namespace Creator\Tests\Mocks;

    class CallableClass {

        function __invoke (AnotherSimpleClass $anotherSimpleClass) : AnotherSimpleClass {
            return $anotherSimpleClass;
        }

    }