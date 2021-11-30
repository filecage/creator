<?php

    namespace Creator;

    use Creator\Exceptions\Unresolvable;
    use Creator\Exceptions\UnresolvableDependency;

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
         * Used for fast dependency tree lookups
         * @var bool[string]
         */
        private $innerDependenciesFlatHashes;

        /**
         * Static memoization of class dependencies to speed up lookups for big trees in larger projects
         * We're caching by class name as a class can not be overwritten
         *
         * @var Dependency[]
         */
        static private $dependencies = [];

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
         * @throws Unresolvable
         */
        static function createFromReflectionParameter(\ReflectionParameter $dependencyParameter) : Dependency {
            $type = $dependencyParameter->getType();

            // TODO: Find a way to support Union types
            if ($type !== null && get_class($type) === 'ReflectionUnionType') {
                throw new Unresolvable("Union Types are unsupported in this version of Creator for parameter `{$dependencyParameter->getName()}` of function `{$dependencyParameter->getDeclaringFunction()->getName()}`");
            }

            // If the parameter refers to a class, we have to find it's inner dependencies
            if ($type !== null && $type->isBuiltin() === false) {
                $dependencyClassName = $type->getName();

                if (isset(static::$dependencies[$dependencyClassName])) {
                    return static::$dependencies[$dependencyClassName];
                } elseif (!class_exists($dependencyClassName, false)) {
                    $dependency = new static(false, $dependencyParameter->getName(), $dependencyClassName, null);
                } else {
                    $dependencyClass = new \ReflectionClass($dependencyParameter->getType()->getName());
                    $dependency = static::createFromCreatable($dependencyParameter->getName(), new Creatable($dependencyClass));
                    static::$dependencies[$dependencyClassName] = $dependency;
                }

            } else {
                $parameterName = $dependencyParameter->getName();
                $dependency = new static(true, $parameterName, '__PARAM__' . $parameterName, null);
            }

            // todo: call as late as possible
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
        function __construct (bool $isPrimitive, string $parameterName, string $dependencyKey, ?DependencyContainer $innerDependencies) {
            $this->isPrimitive = $isPrimitive;
            $this->parameterName = $parameterName;
            $this->dependencyKey = $dependencyKey;
            $this->innerDependencies = $innerDependencies;
            $this->innerDependenciesFlatHashes = [$dependencyKey => true];

            if ($innerDependencies !== null) {
                static::buildInnerDependencyFlatHashes($innerDependencies, $this->innerDependenciesFlatHashes);
            }
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
        function getDependencyKey () : string {
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

        /**
         * @param string ...$dependencyKeys
         *
         * @return bool
         */
        function isDependencyInTree (string ...$dependencyKeys) : bool {
            foreach ($dependencyKeys as $dependencyKey) {
                if (isset($this->innerDependenciesFlatHashes[$dependencyKey])) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param DependencyContainer $dependencies
         * @param array $flatHashMap
         */
        private static function buildInnerDependencyFlatHashes (DependencyContainer $dependencies, array &$flatHashMap) : void {
            foreach ($dependencies->getDependencies() as $dependency) {
                $flatHashMap[$dependency->getDependencyKey()] = true;
                $innerDependencies = $dependency->getInnerDependencies();
                if ($innerDependencies === null) {
                    continue;
                }

                static::buildInnerDependencyFlatHashes($innerDependencies, $flatHashMap);
            }
        }

    }