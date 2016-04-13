<?php

    namespace Creator;

    class Creatable extends InvokableMethod {

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
         * @param string $creationMethodName
         */
        function __construct ($className, $creationMethodName = null) {
            $this->className = $className;
            $this->reflectionClass = new \ReflectionClass($className);
            parent::__construct($creationMethodName !== null ? $this->reflectionClass->getMethod($creationMethodName) : $this->reflectionClass->getConstructor());
        }

        /**
         * @param array|null $args
         *
         * @return object
         */
        function invoke (array $args = null) {
            if ($args !== null) {
                return $this->reflectionClass->newInstanceArgs($args);
            }

            return $this->reflectionClass->newInstance();
        }

        /**
         * @return \ReflectionClass
         */
        function getReflectionClass () {
            return $this->reflectionClass;
        }

    }