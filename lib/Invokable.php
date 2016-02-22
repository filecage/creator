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
         * @var null
         */
        private $object;

        /**
         * @param \ReflectionMethod $reflectionMethod
         * @param null $object
         */
        function __construct(\ReflectionMethod $reflectionMethod = null, $object = null) {
            $this->reflectionMethod = $reflectionMethod;
            $this->object = $object;
        }

        /**
         * @param array $args
         *
         * @return mixed
         */
        function invoke (array $args = []) {
            if (!$this->reflectionMethod) {
                return null;
            }

            if (!$args) {
                return $this->reflectionMethod->invoke($this->object);
            } else {
                return $this->reflectionMethod->invokeArgs($this->object, $args);
            }
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
            if (!$this->reflectionMethod) {
                return $dependencies;
            }

            foreach ($this->reflectionMethod->getParameters() as $parameter) {
                $dependencies->addDependency($parameter);
            }

            return $dependencies;
        }
    }