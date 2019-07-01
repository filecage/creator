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
         * @var DependencyContainer
         */
        private $innerDependencies;

        /**
         * @param \ReflectionFunctionAbstract $reflectionFunction
         * @return \Generator
         * @throws \ReflectionException
         */
        static function yieldFromReflectionFunction (?\ReflectionFunctionAbstract $reflectionFunction) : \Generator {
            if ($reflectionFunction === null) {
                return;
            }

            foreach ($reflectionFunction->getParameters() as $parameter) {
                yield static::createFromReflectionParameter($parameter);
            }
        }

        /**
         * @param \ReflectionParameter $dependencyParameter
         *
         * @return Dependency
         * @throws \ReflectionException
         */
        static function createFromReflectionParameter(\ReflectionParameter $dependencyParameter) : Dependency {
            $parameterName = $dependencyParameter->getName();
            $type = $dependencyParameter->getType();

            // If the parameter refers to a class, we have to find it's inner dependencies
            if ($type !== null && $type->isBuiltin() === false) {
                $dependencyClassName = $type->getName();
                if (!class_exists($dependencyClassName, false)) {
                    $dependency = new static(false, $parameterName, $dependencyClassName, null);
                } else {
                    $dependency = static::createFromCreatable($parameterName, new Creatable($dependencyParameter->getClass()));
                }
            } else {
                $dependency = new static(true, $parameterName, null, null);
            }

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
         * @throws \ReflectionException
         */
        static function createFromCreatable (string $parameterName, Creatable $creatable) : Dependency {
            $innerDependencies = new DependencyContainer(...static::yieldFromReflectionFunction($creatable->getInvokableReflection()));

            return new static(false, $parameterName, $creatable->getReflectionClass()->getName(), $innerDependencies);
        }

        /**
         * @param bool $isPrimitive
         * @param string $parameterName
         * @param string $dependencyKey
         * @param DependencyContainer $innerDependencies
         */
        function __construct (bool $isPrimitive, string $parameterName, ?string $dependencyKey, ?DependencyContainer $innerDependencies) {
            $this->isPrimitive = $isPrimitive;
            $this->parameterName = $parameterName;
            $this->dependencyKey = $dependencyKey;
            $this->innerDependencies = $innerDependencies;
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

        /**
         * @return bool
         */
        function hasInnerDependencies () : bool {
            return $this->innerDependencies !== null;
        }

        /**
         * @return DependencyContainer
         */
        function getInnerDependencies () : ?DependencyContainer {
            return $this->innerDependencies;
        }

    }