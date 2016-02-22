<?php

    namespace Creator;

    use Creator\Exceptions\Unresolvable;
    use Creator\Interfaces\Factory;
    use ReflectionException;
    use ReflectionClass;
    use ReflectionMethod;
    use ReflectionParameter;

    class Creation {

        /**
         * @var string
         */
        private $className;

        /**
         * @var ResourceRegistry
         */
        private $resourceRegistry;

        /**
         * @var ResourceRegistry
         */
        private $injectionRegistry;

        /**
         * @param string $className
         * @param ResourceRegistry $resourceRegistry
         * @param ResourceRegistry $injections
         */
        function __construct ($className, ResourceRegistry $resourceRegistry, ResourceRegistry $injections = null) {
            $this->className = $className;
            $this->resourceRegistry = $resourceRegistry;
            $this->injectionRegistry = $injections ?: new ResourceRegistry();
        }

        /**
         * @return object
         */
        function create () {
            $creatable = new Creatable($this->className);
            $instance = $this->resourceRegistry->getClassResource($this->className);
            if (!$instance) {
                $instance = $this->createInstance($creatable);
                $this->resourceRegistry->registerClassResource($instance);
            }

            return $instance;
        }

        /**
         * @param object $instance
         * @param null $classResourceKey
         *
         * @return $this
         */
        function with ($instance, $classResourceKey = null) {
            $this->injectionRegistry->registerClassResource($instance, $classResourceKey);

            return $this;
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
            $reflector = $creatable->getReflectionClass();
            $constructor = $reflector->getConstructor();
            if (!$constructor) {
                return $reflector->newInstance();
            }

            try {
                return $reflector->newInstanceArgs($this->collectAndResolveMethodDependencies($constructor));
            } catch (ReflectionException $e) {
                throw new Unresolvable('Dependencies can not be resolved: ' . $e->getMessage(), $reflector->getName());
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
         * @param ReflectionMethod $reflectionMethod
         *
         * @return array
         * @throws \Exception
         */
        private function collectAndResolveMethodDependencies (ReflectionMethod $reflectionMethod) {
            $dependencies = [];
            foreach ($reflectionMethod->getParameters() as $parameter) {
                $dependencies[] = $this->resolveParameterDependency($parameter);
            }

            return $dependencies;
        }

        /**
         * @param ReflectionParameter $parameter
         *
         * @return mixed|object
         * @throws Unresolvable
         * @throws ReflectionException
         */
        private function resolveParameterDependency (ReflectionParameter $parameter) {
            $class = $parameter->getClass();
            if ($class) {
                return $this->getClassResource($class->getName());
            }

            try {
                $primitiveResource = $this->getPrimitiveResource($parameter->getName());

                return $primitiveResource;
            } catch (Unresolvable $e) {
                if ($parameter->isDefaultValueAvailable()) {
                    return $parameter->getDefaultValue();
                }
                throw $e;
            }
        }

        /**
         * @param string $classResourceKey
         *
         * @return object
         */
        private function getClassResource ($classResourceKey) {
            $instance = $this->injectionRegistry->getClassResource($classResourceKey) ?: $this->resourceRegistry->getClassResource($classResourceKey);

            if (!$instance) {
                $instance = (new Creation($classResourceKey, $this->resourceRegistry, $this->injectionRegistry))->create();
            }

            return $instance;
        }

        /**
         * @param string $resourceKey
         *
         * @return mixed
         * @throws Unresolvable
         */
        private function getPrimitiveResource ($resourceKey) {
            return $this->resourceRegistry->getPrimitiveResource($resourceKey);
        }

        /**
         * @param string $className
         *
         * @return string
         */
        private function buildFactoryClassName ($className) {
            return sprintf('%sFactory', $className);
        }

        /**
         * @param ReflectionClass $reflector
         *
         * @return Creatable
         * @throws Unresolvable
         */
        private function getFactoryClassCreatable (ReflectionClass $reflector) {
            $factoryClassName = $this->buildFactoryClassName($reflector->getName());
            try {
                $factoryCreatable = new Creatable($factoryClassName);
            } catch (ReflectionException $e) {
                throw new Unresolvable('Can not load factory class "' . $factoryClassName . '": ' . $e->getMessage(), $reflector->getName());
            }

            return $factoryCreatable;
        }
    }