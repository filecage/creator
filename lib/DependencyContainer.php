<?php

    namespace Creator;

    class DependencyContainer {

        /**
         * @var \ReflectionParameter[]
         */
        private $dependencies = [];


        /**
         * @param \ReflectionParameter $dependency
         *
         * @return $this
         */
        function addDependency (\ReflectionParameter $dependency) {
            $this->dependencies[] = $dependency;

            return $this;
        }

        /**
         * @return \ReflectionParameter[]
         */
        function getDependencies () {
            return $this->dependencies;
        }

    }