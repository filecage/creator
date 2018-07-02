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

            // Be careful with default values, there is a *huge* difference between NULL and unset
            if ($dependencyParameter->isDefaultValueAvailable()) {
                $dependency->defaultValue = $dependencyParameter->getDefaultValue();
            }

            return $dependency;
        }

        /**
         * @param string $name
         * @param Creatable $creatable
         *
         * @return Dependency
         */
        static function createFromCreatable (string $name, Creatable $creatable) : Dependency {
            $dependency = new static($name);
            $dependency->class = $creatable->getReflectionClass();

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
            return isset($this->defaultValue);
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