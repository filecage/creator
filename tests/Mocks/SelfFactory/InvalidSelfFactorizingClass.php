<?php

    namespace Creator\Tests\Mocks\SelfFactory;

    use Creator\Interfaces\SelfFactory;
    use Creator\Tests\Mocks\SimpleClass;

    class InvalidSelfFactorizingClass implements SelfFactory {

        /**
         * @return callable
         */
        static function createSelf () : callable {
            return function() {
                return new SimpleClass();
            };
        }

    }