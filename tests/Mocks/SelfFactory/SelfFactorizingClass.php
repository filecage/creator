<?php

    namespace Creator\Tests\Mocks\SelfFactory;

    use Creator\Interfaces\SelfFactory;

    class SelfFactorizingClass implements SelfFactory {

        /**
         * @var ArbitraryClientFromAThirdPartyLibrary
         */
        private $client;

        /**
         * @return callable
         */
        static function createSelf () : callable {
            return function(ConfigProvider $configProvider) : SelfFactorizingClass {
                return new SelfFactorizingClass(new ArbitraryClientFromAThirdPartyLibrary($configProvider->getBaseUrlConfigurationValue(), $configProvider->getAnotherParamterConfigurationValue()));
            };
        }

        /**
         * @param ArbitraryClientFromAThirdPartyLibrary $client
         */
        function __construct (ArbitraryClientFromAThirdPartyLibrary $client) {
            $this->client = $client;
        }

        /**
         * @return string
         */
        function getUrlFromClient () {
            return $this->client->buildRequestUrl();
        }

    }