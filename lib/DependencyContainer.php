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

    }