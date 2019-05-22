<?php

    namespace Creator;

    class Dependency {

        /**
         * @var bool
         */
        private $isPrimitive;

        /**
         * @var string
         */
        private $parameterName;

        /**
         * @var string
         */
        private $dependencyKey;

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
            $isPrimitive = true;
            $dependencyType = null;

            $type = $dependencyParameter->getType();
            if ($type !== null && $type->isBuiltin() === false) {
                $dependencyType = $type->getName();
                $isPrimitive = false;
            }

            $dependency = new static($isPrimitive, $dependencyParameter->getName(), $dependencyType);

            if ($dependencyParameter->isDefaultValueAvailable()) {
                $dependency->isDefaultValueAvailable = true;
                $dependency->defaultValue = $dependencyParameter->getDefaultValue();
            }

            return $dependency;
        }

        /**
         * @param string $parameterName
         * @param Creatable $creatable
         *
         * @return Dependency
         */
        static function createFromCreatable (string $parameterName, Creatable $creatable) : Dependency {
            return new static(false, $parameterName, $creatable->getReflectionClass()->getName());
        }

        /**
         * @param bool $isPrimitive
         * @param string $parameterName
         * @param string $dependencyKey
         */
        function __construct (bool $isPrimitive, string $parameterName, ?string $dependencyKey) {
            $this->isPrimitive = $isPrimitive;
            $this->parameterName = $parameterName;
            $this->dependencyKey = $dependencyKey;
        }

        /**
         * @return string
         */
        function getParameterName () : string {
            return $this->parameterName;
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
         * @return bool
         */
        function isPrimitive () : bool {
            return $this->isPrimitive;
        }

        /**
         * @return string
         */
        function getDependencyKey () : ?string {
            return $this->dependencyKey;
        }

    }