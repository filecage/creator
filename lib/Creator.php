<?php

    namespace Creator;

    use Creator\Exceptions\Unresolvable;
    use Creator\Interfaces\Factory;
    use ReflectionException;
    use ReflectionClass;
    use ReflectionMethod;
    use ReflectionParameter;

    class Creator {

        /**
         * @var array
         */
        private $classResourceRegistry = [];

        /**
         * @var array
         */
        private $primitiveResourceRegistry = [];

        /**
         * @param string $className
         * @param bool $bypassClassResourceRegistry Whether the class will be loaded from (or stored to) registry
         *
         * @throws \Exception
         * @throws Unresolvable
         * @return object
         */
        function create ($className, $bypassClassResourceRegistry = false) {
            $object = $this->resolveClassAgainstRegistry($className);

            if (!$object || $bypassClassResourceRegistry) {
                $object = $this->createInstance(new ReflectionClass($className));

                if ($bypassClassResourceRegistry) {
                    return $object;
                } else {
                    $this->classResourceRegistry[$className] = $object;
                }
            }

            return $object;
        }

        private function createInstance(ReflectionClass $reflector) {
            return ($reflector->isInstantiable()) ? $this->createInstanceFromReflectionClass($reflector) : $this->createInstanceFromUninstantiableReflectionClass($reflector);
        }

        /**
         * @param string $className
         *
         * @return object
         */
        private function resolveClassAgainstRegistry ($className) {
            if (isset($this->classResourceRegistry[$className])) {
                return $this->classResourceRegistry[$className];
            }

            return null;
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
            if (!isset($this->classResourceRegistry[$classResourceKey])) {
                $this->classResourceRegistry[$classResourceKey] = $this->create($classResourceKey);
            }

            return $this->classResourceRegistry[$classResourceKey];
        }

        /**
         * @param string $resourceKey
         *
         * @return mixed
         * @throws Unresolvable
         */
        private function getPrimitiveResource ($resourceKey) {
            if (!isset($this->primitiveResourceRegistry[$resourceKey])) {
                throw new Unresolvable('Tried to load dependency "' . $resourceKey . '" with unknown primitive resource');
            }

            return $this->primitiveResourceRegistry[$resourceKey];
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

        /**
         * @param object $instance
         * @param string $classResourceKey
         *
         * @return $this
         */
        function registerClassResource ($instance, $classResourceKey = null) {
            $classResourceKey = $classResourceKey ?: get_class($instance);
            $this->classResourceRegistry[$classResourceKey] = $instance;

            return $this;
        }

        /**
         * @param string $resourceKey
         * @param mixed $value
         *
         * @return $this
         */
        function registerPrimitiveResource ($resourceKey, $value) {
            $this->primitiveResourceRegistry[$resourceKey] = $value;

            return $this;
        }

        function getCacheCount () {
            return count($this->classResourceRegistry);
        }
    }