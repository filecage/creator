<?php

    namespace Creator;

    class Dependency {

        /**
         * @var string
         */
        private $name;

        /**
         * @var \ReflectionClass
         */
        private $class;

        /**
         * @var bool
         */
        private $isDefaultValueAvailable = false;

        /**
         * @var mixed
         */
        private $defaultValue;

        /**
         * @param \ReflectionParameter $dependencyParameter
         *
         * @return Dependency
         */
        static function createFromReflectionParameter(\ReflectionParameter $dependencyParameter) : Dependency {
            $dependency = new static($dependencyParameter->getName());
            $dependency->class = $dependencyParameter->getClass();

            if ($dependencyParameter->isDefaultValueAvailable()) {
                $dependency->isDefaultValueAvailable = true;
                $dependency->defaultValue = $dependencyParameter->getDefaultValue();
            }

            return $dependency;
        }

        /**
         * @param string $name
         */
        function __construct (string $name) {
            $this->name = $name;
        }

        /**
         * @return string
         */
        function getName () : string {
            return $this->name;
        }

        /**
         * @return bool
         */
        function isDefaultValueAvailable () : bool {
            return $this->isDefaultValueAvailable;
        }

        /**
         * @return mixed
         */
        function getDefaultValue () {
            return $this->defaultValue;
        }

        /**
         * @return null|\ReflectionClass
         */
        function getClass () : ?\ReflectionClass {
            return $this->class;
        }

    }