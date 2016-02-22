<?php

    namespace Creator;

    class Invokable {

        /**
         * @var DependencyContainer
         */
        private $dependencies;

        /**
         * @var \ReflectionMethod
         */
        private $reflectionMethod;

        /**
         * @param \ReflectionMethod $reflectionMethod
         */
        function __construct(\ReflectionMethod $reflectionMethod) {
            $this->reflectionMethod = $reflectionMethod;
        }

        /**
         * @return \ReflectionMethod
         */
        function getReflectionMethod () {
            return $this->reflectionMethod;
        }

        /**
         * @return DependencyContainer
         */
        function getDependencies () {
            if (!isset($this->dependencies)) {
                $this->dependencies = $this->collectDependencies();
            }

            return $this->dependencies;
        }

        /**
         * @return DependencyContainer
         */
        private function collectDependencies () {
            $dependencies = new DependencyContainer();
            foreach ($this->reflectionMethod->getParameters() as $parameter) {
                $dependencies->addDependency($parameter);
            }

            return $dependencies;
        }
    }