<?php

    namespace Creator;

    class Creatable {
        /**
         * @var string
         */
        private $className;

        /**
         * @var \ReflectionClass
         */
        private $reflectionClass;

        /**
         * @param string $className
         */
        function __construct ($className) {
            $this->className = $className;
            $this->reflectionClass = new \ReflectionClass($className);
        }

        /**
         * @return \ReflectionClass
         */
        function getReflectionClass () {
            return $this->reflectionClass;
        }

    }