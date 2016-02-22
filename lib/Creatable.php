<?php

    namespace Creator;

    class Creatable extends Invokable {

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
         * @return \ReflectionClass
         */
        function getReflectionClass () {
            return $this->reflectionClass;
        }

    }