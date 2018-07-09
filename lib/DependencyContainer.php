<?php

    namespace Creator;

    class DependencyContainer {

        /**
         * @var Dependency[]
         */
        private $dependencies = [];


        /**
         * @param Dependency $dependency
         *
         * @return $this
         */
        function addDependency (Dependency $dependency) {
            $this->dependencies[] = $dependency;

            return $this;
        }

        /**
         * @return Dependency[]
         */
        function getDependencies () {
            return $this->dependencies;
        }

        /**
         * @param DependencyContainer $dependencyContainer
         *
         * @return DependencyContainer
         */
        function mergeWith (DependencyContainer $dependencyContainer) {
            $clone = clone $this;
            $clone->dependencies = array_merge($clone->dependencies, array_filter($dependencyContainer->dependencies, function(Dependency $dependency) use ($clone){
                return !in_array($dependency, $clone->dependencies);
            }));

            return $clone;
        }

    }