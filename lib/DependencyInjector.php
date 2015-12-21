<?php

    namespace treething;

    use ReflectionException;
    use ReflectionClass;
    use ReflectionMethod;
    use ReflectionParameter;
    use Slim\Container;

    class DependencyInjector {

        /**
         * @var array
         */
        private $classResourceRegistry = [];

        /**
         * @var array
         */
        private $containerLookupMap = [];

        /**
         * @var array
         */
        private $primitiveResourceRegistry = [];

        /**
         * @var Container
         */
        private $container;

        /**
         * @param string $className
         * @param bool $bypassClassResourceRegistry Whether the class will be loaded from (or stored to) registry
         *
         * @throws \Exception
         * @return object
         */
        function create ($className, $bypassClassResourceRegistry = false) {
            $object = $this->resolveClassAgainstRegistry($className);

            if (!$object || $bypassClassResourceRegistry) {
                $reflector = new ReflectionClass($className);
                $object = ($reflector->isInstantiable()) ? $this->createInstanceFromReflectionClass($reflector) : $this->createInstanceFromUninstantiableReflectionClass($reflector);

                if ($bypassClassResourceRegistry) {
                    return $object;
                } else {
                    $this->classResourceRegistry[$className] = $object;
                }
            }

            return $object;
        }

        private function resolveClassAgainstRegistry ($className) {
            if (isset($this->classResourceRegistry[$className])) {
                return $this->classResourceRegistry[$className];
            }

            if (isset($this->container)) {
                // update container lookup map
                $unknownContainerKeys = array_diff($this->container->keys(), array_column($this->containerLookupMap, 'key'));
                foreach ($unknownContainerKeys as $key) {
                    $object = $this->container->get($key);
                    if (!is_object($object)) {
                        continue;
                    }

                    $this->containerLookupMap[get_class($object)] = [
                        'key' => $key,
                        'object' => $object
                    ];
                }

                if (isset($this->containerLookupMap[$className])) {
                    return $this->containerLookupMap[$className]['object'];
                }
            }

            return null;
        }

        /**
         * @param ReflectionClass $reflector
         *
         * @return object
         * @throws \Exception
         */
        private function createInstanceFromUninstantiableReflectionClass (ReflectionClass $reflector) {
            if ($reflector->implementsInterface(Interfaces\Singleton::class)) {
                return $this->getInstanceFromSingleton($reflector);
            } else {
                throw new \Exception('Class is neither instantiable nor implements Singleton interface', $reflector->getName());
            }
        }

        /**
         * @param ReflectionClass $reflector
         *
         * @throws \Exception
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
                throw new \Exception('Dependencies can not be resolved: ' . $e->getMessage(), $reflector->getName());
            }
        }

        private function createInstanceFromFactoryReflector (ReflectionClass $reflector, ReflectionClass $factoryReflector) {
            // todo: implement with interface and type check
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
         * @throws \Exception
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
            } catch (\Exception $e) {
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
         * @throws \Exception
         */
        private function getPrimitiveResource ($resourceKey) {
            if (!isset($this->primitiveResourceRegistry[$resourceKey])) {
                throw new \Exception('Tried to load dependency "' . $resourceKey . '" with unknown primitive resource');
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
         * @throws \Exception
         */
        private function getFactoryClassReflector (ReflectionClass $reflector) {
            $factoryClassName = $this->buildFactoryClassName($reflector->getName());
            try {
                $factoryClass = new ReflectionClass($factoryClassName);
            } catch (ReflectionException $e) {
                throw new \Exception('Can not load factory class "' . $factoryClassName . '": ' . $e->getMessage(), $reflector->getName());
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
         * @param Container $container
         * @return $this
         */
        function registerDiContainer (Container $container) {
            $this->container = $container;

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