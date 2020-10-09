<?php

    namespace Creator;

    class ResourceRegistry {

        /**
         * @var ClassResource[]
         */
        private $classResources = [];

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
         * @param string $classResourceKey
         *
         * @return null|\ReflectionClass
         */
        function getClassResourceReflection ($classResourceKey) : ?\ReflectionClass {
            if (!isset($this->classResources[$classResourceKey])) {
                return null;
            }

            return $this->classResources[$classResourceKey]->getReflection();
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
            $fulfillable = $creatable->getReflectionClass();
            if ($fulfillable->isInterface()) {
                $verificationCallback = function(ClassResource $resource) use ($fulfillable) {
                    return $resource->implementsInterface($fulfillable->getName());
                };
            } elseif ($fulfillable->isAbstract()) {
                $verificationCallback = function(ClassResource $resource) use ($fulfillable) {
                    return $fulfillable->isInstance($resource->getInstance());
                };
            } else {
                // unsupported uninstantiable
                return null;
            }

            foreach ($this->classResources as $resource) {
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
         * @return bool
         */
        function containsAnyOf (DependencyContainer $dependencyContainer) {
            return $dependencyContainer->containsClassDependency(...array_keys($this->classResources));
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
         * @param callable $callback
         *
         * @return $this
         */
        private function onRegistration (callable $callback) {
            $this->onRegistration = $callback;

            return $this;
        }

    }