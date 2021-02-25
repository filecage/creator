<?php

    namespace Creator;

    class ResourceRegistry {

        /**
         * @var ClassResource[]
         */
        private $classResources = [];

        /**
         * Keeps all resource keys for easier access without using `array_keys()`
         * @var string[]
         */
        private $classResourceKeys = [];

        /**
         * @var array
         */
        private $primitiveResources = [];

        /**
         * @var Invokable[]
         */
        private $factories = [];

        /**
         * @var callable
         */
        private $onRegistration;

        /**
         * @param object $instance
         * @param string ...$classResourceKeys
         *
         * @return $this
         */
        function registerClassResource ($instance, ...$classResourceKeys) {
            $resource = ClassResource::createFromInstance($instance);
            if (empty($classResourceKeys)) {
                $classResourceKeys[] = get_class($instance);
            }

            foreach ($classResourceKeys as $classResourceKey) {
                if (isset($this->classResources[$classResourceKey])) {
                    continue;
                }

                $this->classResourceKeys[] = $classResourceKey;
                $this->classResources[$classResourceKey] = $resource;
            }

            if ($this->onRegistration) {
                call_user_func($this->onRegistration, $instance, ...$classResourceKeys);
            }

            return $this;
        }

        /**
         * @param string $classResourceKey
         * @return $this
         */
        function resetClassResource ($classResourceKey) {
            unset($this->classResources[$classResourceKey]);
            unset($this->classResourceKeys[array_search($classResourceKey, $this->classResourceKeys)]);
            // unsetting at the given index is slightly faster than copying the full array
            // however it is dangerous if you can not be sure that the index exists (otherwise index 0 will be unset)

            return $this;
        }

        /**
         * @param string $classResourceKey
         *
         * @return object
         */
        function getClassResource ($classResourceKey) {
            if (!isset($this->classResources[$classResourceKey])) {
                return null;
            }

            return $this->classResources[$classResourceKey]->getInstance();
        }

        /**
         * @return string[]
         */
        function getClassResourceKeys () : array {
            return $this->classResourceKeys;
        }

        /**
         * @param string $classResourceKey
         * @param Invokable $factory
         *
         * @return $this
         */
        function registerFactoryForClassResource (string $classResourceKey, Invokable $factory) {
            $this->factories[$classResourceKey] = $factory;

            return $this;
        }

        /**
         * @param string $classResourceKey
         * @return bool
         */
        function hasFactoryForClassResource (string $classResourceKey) : bool {
            return isset($this->factories[$classResourceKey]);
        }

        /**
         * @param string $classResourceKey
         *
         * @return Invokable|null
         */
        function getFactoryInvokableForClassResource (string $classResourceKey) : ?Invokable {
            return $this->factories[$classResourceKey] ?? null;
        }

        /**
         * @param Creatable $creatable
         * @return object
         */
        function findFulfillingInstance (Creatable $creatable) {
            foreach ($this->classResources as $resource) {
                if (!isset($verificationCallback)) {
                    // all reflection calls are expensive, so we're saving them for all registries that are empty anyway
                    $verificationCallback = $this->createFulfillableAsserter($creatable);
                    if ($verificationCallback === null) {
                        return null; // not fulfillable, the class is neither an abstract nor an interface
                    }
                }

                if ($verificationCallback($resource) === true) {
                    return $resource->getInstance();
                }
            }

            // not fulfillable
            return null;
        }

        /**
         * @return int
         */
        function getRegisteredClassResourcesCount () {
            return count($this->classResources);
        }

        /**
         * @param string $resourceKey
         * @param mixed $value
         *
         * @return $this
         */
        function registerPrimitiveResource ($resourceKey, $value) {
            $this->primitiveResources[$resourceKey] = $value;

            return $this;
        }

        /**
         * @param string $resourceKey
         * @return string
         */
        function hasPrimitiveResource (string $resourceKey) : string {
            return array_key_exists($resourceKey, $this->primitiveResources);
        }

        /**
         * @param string $resourceKey
         *
         * @return mixed
         */
        function getPrimitiveResource ($resourceKey) {
            return $this->primitiveResources[$resourceKey] ?? null;
        }

        /**
         * @param DependencyContainer $dependencyContainer
         *
         * @deprecated
         * @return bool
         */
        function containsAnyOf (DependencyContainer $dependencyContainer) {
            return $dependencyContainer->containsClassDependency(...$this->classResourceKeys);
        }

        /**
         * @param string $exceptedClass
         *
         * @return ResourceRegistry
         */
        function cloneWithout ($exceptedClass) {
            $clone = clone $this;
            unset($clone->classResources[$exceptedClass]);
            $clone->onRegistration(function($instance, ...$classes) use ($exceptedClass){
                $classes = array_filter($classes, function($class) use ($exceptedClass) {
                    return $class !== $exceptedClass;
                });

                if (!empty($classes)) {
                    $this->registerClassResource($instance, ...$classes);
                }
            });

            return $clone;
        }

        /**
         * @param Creatable $creatable
         *
         * @return callable|null
         */
        private function createFulfillableAsserter (Creatable $creatable) : ?callable {
            $fulfillable = $creatable->getReflectionClass();
            if ($fulfillable->isInterface()) {
                return function(ClassResource $resource) use ($fulfillable) {
                    return $resource->implementsInterface($fulfillable->getName());
                };
            } elseif ($fulfillable->isAbstract()) {
                return function(ClassResource $resource) use ($fulfillable) {
                    return $fulfillable->isInstance($resource->getInstance());
                };
            } else {
                // unsupported uninstantiable
                return null;
            }
        }

        /**
         * @param callable $callback
         *
         * @return $this
         */
        private function onRegistration (callable $callback) {
            $this->onRegistration = $callback;

            return $this;
        }

    }