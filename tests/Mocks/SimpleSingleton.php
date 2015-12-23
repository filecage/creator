<?php

    namespace Creator\Tests\Mocks;

    use Creator\Interfaces\Singleton;

    class SimpleSingleton implements Singleton {

        /**
         * @var static
         */
        static $instance;

        private function __construct () {}

        /**
         * @return SimpleSingleton
         */
        static function getInstance () {
            if (!isset(static::$instance)) {
                static::$instance = new static;
            }

            return static::$instance;
        }

    }