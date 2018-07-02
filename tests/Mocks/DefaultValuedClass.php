<?php

    namespace Creator\Tests\Mocks;

    class DefaultValuedClass {

        /**
         * @var string
         */
        private $defaultStringValue;
        /**
         * @var int
         */
        private $defaultIntValue;
        /**
         * @var null
         */
        private $defaultNullValue;

        /**
         * @param string $defaultStringValue
         * @param int $defaultIntValue
         * @param null $defaultNullValue
         */
        function __construct ($defaultStringValue = 'foobar', $defaultIntValue = 123, $defaultNullValue = null) {
            $this->defaultStringValue = $defaultStringValue;
            $this->defaultIntValue = $defaultIntValue;
            $this->defaultNullValue = $defaultNullValue;
        }

        /**
         * @return string
         */
        function getDefaultStringValue () {
            return $this->defaultStringValue;
        }

        /**
         * @return int
         */
        function getDefaultIntValue () {
            return $this->defaultIntValue;
        }

        /**
         * @return null
         */
        function getDefaultNullValue () {
            return $this->defaultNullValue;
        }

    }