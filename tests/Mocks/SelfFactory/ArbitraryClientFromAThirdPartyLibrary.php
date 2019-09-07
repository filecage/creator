<?php

    namespace Creator\Tests\Mocks\SelfFactory;

    /**
     * To explain the SelfFactory pattern we act like this class is from a third party library.
     *
     * Normally it would be necessary to have a global factory to create an instance of this class or
     * break the dependency injection pattern by creating this dependency internally in our consumer.
     */
    class ArbitraryClientFromAThirdPartyLibrary {

        /**
         * @var string
         */
        private $baseUrl;

        /**
         * @var int
         */
        private $anotherParameter;

        /**
         * @param string $baseUrl
         * @param int $anotherParameter
         */
        function __construct (string $baseUrl, int $anotherParameter) {
            $this->baseUrl = $baseUrl;
            $this->anotherParameter = $anotherParameter;
        }

        /**
         * @return string
         */
        function getBaseUrl () : string {
            return $this->baseUrl;
        }

        /**
         * @return string
         */
        function getAnotherParamter () : string {
            return $this->anotherParameter;
        }

        /**
         * @return string
         */
        function buildRequestUrl () : string {
            return sprintf('%s?parameter=%d', $this->baseUrl, $this->anotherParameter);
        }

    }