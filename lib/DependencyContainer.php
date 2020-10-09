<?php

    namespace Creator;

    class DependencyContainer {

        /**
         * @var Dependency[]
         */
        private $dependencies;

        /**
         * Faster way to keep track of dependency trees
         *
         * @var string[true]
         */
        private $dependencyKeysIncluded = [];

        /**
         * @param Dependency[] $dependencies
         */
        function __construct (Dependency ...$dependencies) {
            $this->dependencies = $dependencies;
            foreach ($dependencies as $dependency) {
                $this->dependencyKeysIncluded[$dependency->getDependencyKey()] = true;
            }
        }

        /**
         * @param Dependency $dependency
         *
         * @return $this
         */
        function addDependency (Dependency $dependency) : self {
            $this->dependencies[] = $dependency;
            $this->dependencyKeysIncluded[$dependency->getDependencyKey()] = true;

            return $this;
        }

        /**
         * @return Dependency[]
         */
        function getDependencies () : array {
            return $this->dependencies;
        }

        /**
         * @return \Generator
         */
        function getFlatDependencyIterator () : \Generator {
            foreach ($this->dependencies as $dependency) {
                yield $dependency;

                if ($dependency->hasInnerDependencies()) {
                    yield from $dependency->getInnerDependencies()->getFlatDependencyIterator();
                }
            }
        }

        /**
         * @param string ...$classNames
         *
         * @return bool
         */
        function containsClassDependency (string ...$classNames) : bool {
            foreach ($classNames as $className) {
                if (isset($this->dependencyKeysIncluded[$className])) {
                    return true;
                }
            }

            foreach ($this->dependencies as $dependency) {
                $innerDependencies = $dependency->getInnerDependencies();
                if ($innerDependencies !== null && $innerDependencies->containsClassDependency(...$classNames)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param DependencyContainer $dependencyContainer
         *
         * @return DependencyContainer
         */
        function mergeWith (DependencyContainer $dependencyContainer) : self {
            $clone = clone $this;
            $clone->dependencies = array_merge($clone->dependencies, array_filter($dependencyContainer->dependencies, function(Dependency $dependency) use ($clone){
                return !in_array($dependency, $clone->dependencies);
            }));

            return $clone;
        }

    }