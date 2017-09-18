<?php

    namespace Creator;

    use Creator\Exceptions\Unresolvable;
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
            $this->creatable = new Creatable($this->className);
            parent::__construct($this->creatable, $resourceRegistry, $injections);
        }

        /**
         * @return object
         * @throws Unresolvable
         */
        function create () {
            $creatable = $this->creatable;

            try {
                // todo: ensure a recreation if ANY instance inside the dependency tree requires an injected  instance (#1)
                if ($this->injectionRegistry->containsAnyOf($creatable->getDependencies())) {
                    return $this->createInstance($creatable);
                }
            } catch (ReflectionException $e) {
                throw new Unresolvable('Dependencies can not be resolved', $creatable->getReflectionClass()->getName());
            }

            $instance = $this->resourceRegistry->getClassResource($this->className);
            if (!$instance) {
                $instance = $this->createInstance($creatable);
                $this->resourceRegistry->registerClassResource($instance);
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
            return ($creatable->getReflectionClass()->isInstantiable()) ? $this->createInstanceFromCreatable($creatable) : $this->createInstanceFromUninstantiableCreatable($creatable);
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
                return $this->createInstanceFromFactoryCreatable($creatable, $this->getFactoryClassCreatable($reflector));
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
            try {
                return (new Invocation($creatable, $this->resourceRegistry, $this->injectionRegistry))->invoke();
            } catch (ReflectionException $e) {
                throw new Unresolvable('Dependencies can not be resolved: ' . $e->getMessage(), $creatable->getReflectionClass()->getName());
            }
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
                throw new Unresolvable('Factory ' . $factoryReflector->getName() . ' does not implement required interface Creator\\Interfaces\\Factory', $reflector->getName());
            }

            $class = $factory->createInstance();
            if (!$reflector->isInstance($class)) {
                throw new Unresolvable('Create method of factory ' . $factoryReflector->getName() . ' did not return instance of ' . $reflector->getName() . ' class', $reflector->getName());
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
                throw new Unresolvable('Can not load factory for uninstantiable class "' . $className . '": ' . $e->getMessage(), $reflector->getName());
            }

            return $factoryCreatable;
        }
    }