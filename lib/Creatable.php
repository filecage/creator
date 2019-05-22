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
         * @var string|null
         */
        private $creationMethodName;

        /**
         * @param string $className
         * @param string $creationMethodName
         * @throws \ReflectionException
         */
        function __construct ($className, $creationMethodName = null) {
            $this->className = $className;
            $this->reflectionClass = new \ReflectionClass($className);
            $this->creationMethodName = $creationMethodName;
            parent::__construct($creationMethodName !== null ? $this->reflectionClass->getMethod($creationMethodName) : $this->reflectionClass->getConstructor());
        }

        /**
         * @return string
         */
        function getName () : string {
            return $this->className . '::' . ($this->creationMethodName ?? $this->reflectionClass->getConstructor()->getName()) . '()';
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