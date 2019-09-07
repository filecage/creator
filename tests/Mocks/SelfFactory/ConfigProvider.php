<?php

    namespace Creator\Tests\Mocks\SelfFactory;

    /**
     * This class acts as a configurable config provider in our project. It holds the values that need to be
     * injected into our third-party client library.
     */
    class ConfigProvider {

        /**
         * @return string
         */
        function getBaseUrlConfigurationValue () : string {
            return 'http://localhost/creator';
        }

        /**
         * @return int
         */
        function getAnotherParamterConfigurationValue () : int {
            return 42;
        }

    }