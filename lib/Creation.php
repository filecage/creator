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
            $resource = $resourceRegistry->getClassResourceReflection($className);

            try {
                $this->creatable = ($resource !== null) ? new Creatable($resource) : Creatable::createFromClassName($this->className);
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

            $instance = $this->createInstanceWithRegistry($this->className, $creatable, $this->injectionRegistry, true) ?? $this->createInstanceWithRegistry($this->className, $creatable, $this->resourceRegistry, false);
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
         * @param bool $recreate Defines whether this registry qualifies for recreations (only for injected registry)
         *
         * @return mixed|object
         * @throws CreatorException
         * @throws Unresolvable
         */
        private function createInstanceWithRegistry (string $className, Creatable $creatable, ResourceRegistry $registry, bool $recreate) {
            // Does the registry already contain the resource?
            $instance = $registry->getClassResource($className) ?? $registry->findFulfillingInstance($creatable);
            if ($instance !== null) {
                return $instance;
            }

            if ($recreate === true) {
                try {
                    // Does the registry contain a dependency that is required for this resource?
                    if ($registry->containsAnyOf($creatable->getDependencies())) {
                        $instance = $this->createInstance($creatable);
                    }
                } catch (Unresolvable $exception) {
                    /** @var Unresolvable $deferredException */
                    $deferredException = $exception;
                }
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
                $instance = $this->findsFulfillngInstanceForUninstantiableCreatable($creatable);
                if ($instance !== null) {
                    return $instance;
                }
            }

            throw new Unresolvable('Class is neither instantiable nor implements Singleton interface', $reflector->getName());
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

    }