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
            $instance = $this->resourceRegistry->getClassResource($this->className);
            if (!$instance) {
                $instance = $this->createInstance(new ReflectionClass($this->className));
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
         * @param ReflectionClass $reflector
         *
         * @return object
         * @throws Unresolvable
         */
        private function createInstance(ReflectionClass $reflector) {
            return ($reflector->isInstantiable()) ? $this->createInstanceFromReflectionClass($reflector) : $this->createInstanceFromUninstantiableReflectionClass($reflector);
        }

        /**
         * @param ReflectionClass $reflector
         *
         * @return object
         * @throws Unresolvable
         */
        private function createInstanceFromUninstantiableReflectionClass (ReflectionClass $reflector) {
            if ($reflector->implementsInterface(Interfaces\Singleton::class)) {
                return $this->getInstanceFromSingleton($reflector);
            } elseif ($reflector->isInterface() || $reflector->isAbstract()) {
                $factoryReflector = $this->getFactoryClassReflector($reflector);

                return $this->createInstanceFromFactoryReflector($reflector, $factoryReflector);
            } else {
                throw new Unresolvable('Class is neither instantiable nor implements Singleton interface', $reflector->getName());
            }
        }

        /**
         * @param ReflectionClass $reflector
         *
         * @throws Unresolvable
         * @return object
         */
        private function createInstanceFromReflectionClass (ReflectionClass $reflector) {
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
         * @param ReflectionClass $reflector
         * @param ReflectionClass $factoryReflector
         *
         * @return $this
         * @throws Unresolvable
         */
        private function createInstanceFromFactoryReflector (ReflectionClass $reflector, ReflectionClass $factoryReflector) {
            $factory = $this->createInstance($factoryReflector);
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
         * @return ReflectionClass
         * @throws Unresolvable
         */
        private function getFactoryClassReflector (ReflectionClass $reflector) {
            $factoryClassName = $this->buildFactoryClassName($reflector->getName());
            try {
                $factoryClass = new ReflectionClass($factoryClassName);
            } catch (ReflectionException $e) {
                throw new Unresolvable('Can not load factory class "' . $factoryClassName . '": ' . $e->getMessage(), $reflector->getName());
            }

            return $factoryClass;
        }
    }