<?php

    namespace Creator;

    class FactoryCreatable extends Creatable {

        /**
         * @param string $className
         */
        function __construct ($className) {
            parent::__construct($this->buildFactoryClassName($className), $this->buildFactoryClassName($className));
        }

        /**
         * @param string $className
         *
         * @return string
         */
        private function buildFactoryClassName ($className) {
            return sprintf('%sFactory', $className);
        }

    }