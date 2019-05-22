<?php

    namespace Creator;

    abstract class Invokable {

        /**
         * @var DependencyContainer
         */
        private $dependencies;

        /**
         * @var \ReflectionFunction|\ReflectionMethod
         */
        protected $invokableReflection;

        /**
         * @param array $args
         *
         * @return mixed
         */
        abstract function invoke (array $args = []);

        /**
         * @param \ReflectionFunctionAbstract $invokableReflection
         */
        function __construct (?\ReflectionFunctionAbstract $invokableReflection) {
            $this->invokableReflection = $invokableReflection;
        }

        /**
         * @return string
         */
        abstract function getName () : ?string;

        /**
         * @return \ReflectionMethod
         */
        function getInvokableReflection () {
            return $this->invokableReflection;
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
            if (!$this->invokableReflection) {
                return $dependencies;
            }

            foreach ($this->invokableReflection->getParameters() as $parameter) {
                $dependencies->addDependency(Dependency::createFromReflectionParameter($parameter));
            }

            return $dependencies;
        }
    }