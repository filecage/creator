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
         * @param string|null $creationMethodName
         * @return Creatable
         * @throws \ReflectionException
         */
        static function createFromClassName (string $className, string $creationMethodName = null) : self {
            return new static(new \ReflectionClass($className), $creationMethodName);
        }

        /**
         * @param \ReflectionClass $reflectionClass
         * @param null $creationMethodName
         * @throws \ReflectionException
         */
        function __construct (\ReflectionClass $reflectionClass, $creationMethodName = null) {
            $this->className = $reflectionClass->getName();
            $this->reflectionClass = $reflectionClass;
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