<?php

    namespace Creator;

    class DependencyContainer {

        /**
         * @var Dependency[]
         */
        private $dependencies;

        /**
         * @param Dependency[] $dependencies
         */
        function __construct (Dependency ...$dependencies) {
            $this->dependencies = $dependencies;
        }

        /**
         * @param Dependency $dependency
         *
         * @return $this
         */
        function addDependency (Dependency $dependency) : self {
            $this->dependencies[] = $dependency;

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