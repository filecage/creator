<?php

    namespace Creator\Tests\Mocks;

    class ArbitraryClassWithStringValue {
        /**
         * @var string
         */
        private $stringValue;

        /**
         * @param string $stringValue
         */
        function __construct (string $stringValue) {
            $this->stringValue = $stringValue;
        }

        /**
         * @return string
         */
        function getStringValue () : string {
            return $this->stringValue;
        }

    }