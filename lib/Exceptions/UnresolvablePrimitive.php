<?php

    namespace Creator\Exceptions;

    /**
     * @internal This class is for internal usage only
     */
    class UnresolvablePrimitive extends Unresolvable {

        /**
         * @var string
         */
        private $resourceKey;

        /**
         * @param string $resourceKey
         */
        function __construct (string $resourceKey) {
            parent::__construct('Unknown primitive resource: ' . $resourceKey);
            $this->resourceKey = $resourceKey;
        }

        /**
         * @return string
         */
        function getResourceKey () : string {
            return $this->resourceKey;
        }

    }