<?php

    namespace Creator;

    use Creator\Exceptions\CreatorException;
    use Creator\Exceptions\Unreflectable;
    use Creator\Exceptions\Unresolvable;
    use Creator\Exceptions\UnresolvableDependency;
    use Creator\Interfaces\Factory;
    use ReflectionException;
    use ReflectionClass;

    class Creation extends Invocation {

        /**
         * @var string
         */
        private $className;

        /**
         * @var Creatable
         */
        private $creatable;

        /**
         * @param string $className
         * @param ResourceRegistry $resourceRegistry
         * @param ResourceRegistry $injections
         */
        function __construct ($className, ResourceRegistry $resourceRegistry, ResourceRegistry $injections = null) {
            $this->className = $className;
            try {
                $this->creatable = new Creatable($this->className);
            } catch (\ReflectionException $reflectionException) {
                throw new Unreflectable($className, $reflectionException->getMessage());
            }

            parent::__construct($this->creatable, $resourceRegistry, $injections);
        }

        /**
         * @return object
         * @throws Unresolvable
         */
        function create () {
            $creatable = $this->creatable;

            $instance = $this->createInstanceWithRegistry($this->className, $creatable, $this->injectionRegistry) ?? $this->createInstanceWithRegistry($this->className, $creatable, $this->resourceRegistry);
            if (!$instance) {
                $instance = $this->createInstance($creatable);
                $this->resourceRegistry->registerClassResource($instance);
            }

            return $instance;
        }

        /**
         * @param string $className
         * @param Creatable $creatable
         * @param ResourceRegistry $registry
         *
         * @return mixed|object
         * @throws CreatorException
         * @throws Unresolvable
         */
        private function createInstanceWithRegistry (string $className, Creatable $creatable, ResourceRegistry $registry) {
            // Does the registry already contain the resource?
            $instance = $registry->getClassResource($className) ?? $registry->findFulfillingInstance($creatable);
            if ($instance !== null) {
                return $instance;
            }

            try {
                // Does the registry contain a dependency that is required for this resource?
                if ($registry->containsAnyOf($creatable->getDependencies())) {
                    // todo: ensure a recreation if ANY instance inside the dependency tree requires an injected  instance (#1)
                    $instance = $this->createInstance($creatable);
                }
            } catch (Unresolvable $exception) {
                /** @var Unresolvable $deferredException */
                $deferredException = $exception;
            }

            // Does the registry contain a factory for this resource?
            if ($instance === null) {
                $instance = $registry->getFactoryInvokableForClassResource($className);
                if ($instance !== null) {
                    $instance = (new Fabrication($className, $instance, $this->resourceRegistry, $this->injectionRegistry, $registry))->fabricate();
                }
            } else {
                $registry->registerClassResource($instance);
            }

            if ($instance === null && isset($deferredException)) {
                throw $deferredException;
            }

            return $instance;
        }

        /**
         * @param Creatable $creatable
         *
         * @return object
         * @throws Unresolvable
         */
        private function createInstance(Creatable $creatable) {
            try {
                return ($creatable->getReflectionClass()->isInstantiable()) ? $this->createInstanceFromCreatable($creatable) : $this->createInstanceFromUninstantiableCreatable($creatable);
            } catch (UnresolvableDependency $unresolvableDependency) {
                throw $unresolvableDependency->setParentInvokableName($this->creatable->getName());
            }
        }

        /**
         * @param Creatable $creatable
         *
         * @return object
         * @throws Unresolvable
         */
        private function createInstanceFromUninstantiableCreatable (Creatable $creatable) {
            $reflector = $creatable->getReflectionClass();
            if ($reflector->implementsInterface(Interfaces\Singleton::class)) {
                return $this->getInstanceFromSingleton($reflector);
            } elseif ($reflector->isInterface() || $reflector->isAbstract()) {
                return $this->findsFulfillngInstanceForUninstantiableCreatable($creatable) ?? $this->createInstanceFromFactoryCreatable($creatable, $this->getFactoryClassCreatable($reflector));
            } else {
                throw new Unresolvable('Class is neither instantiable nor implements Singleton interface', $reflector->getName());
            }
        }

        /**
         * @param Creatable $creatable
         *
         * @return object
         * @throws Unresolvable
         */
        private function createInstanceFromCreatable (Creatable $creatable) {
            return (new Invocation($creatable, $this->resourceRegistry, $this->injectionRegistry))->invoke();
        }

        /**
         * @param Creatable $creatable
         * @param Creatable $factory
         *
         * @return object
         * @throws Unresolvable
         */
        private function createInstanceFromFactoryCreatable (Creatable $creatable, Creatable $factory) {
            $factoryReflector = $factory->getReflectionClass();
            $reflector = $creatable->getReflectionClass();

            $factory = $this->createInstance($factory);
            if (!$factory instanceof Factory) {
                throw new Unresolvable('Factory `' . $factoryReflector->getName() . '` does not implement required interface Creator\\Interfaces\\Factory', $reflector->getName());
            }

            $class = $factory->createInstance();
            if (!$reflector->isInstance($class)) {
                throw new Unresolvable('Create method of factory `' . $factoryReflector->getName() . '` did not return instance of `' . $reflector->getName() . '` class', $reflector->getName());
            }

            return $class;
        }

        /**
         * @param ReflectionClass $reflector
         *
         * @return object
         */
        private function getInstanceFromSingleton (ReflectionClass $reflector) {
            return $reflector->getMethod('getInstance')->invoke(null);
        }

        /**
         * @param Creatable $creatable
         * @return object|null
         */
        private function findsFulfillngInstanceForUninstantiableCreatable (Creatable $creatable) {
            return $this->injectionRegistry->findFulfillingInstance($creatable) ?? $this->resourceRegistry->findFulfillingInstance($creatable);
        }

        /**
         * @param ReflectionClass $reflector
         *
         * @return Creatable
         * @throws Unresolvable
         */
        private function getFactoryClassCreatable (ReflectionClass $reflector) {
            $className = $reflector->getName();
            try {
                $factoryCreatable = new FactoryCreatable($className);
            } catch (ReflectionException $e) {
                throw new Unresolvable('Can not load factory for uninstantiable class `' . $className . '`: ' . $e->getMessage(), $reflector->getName());
            }

            return $factoryCreatable;
        }
    }