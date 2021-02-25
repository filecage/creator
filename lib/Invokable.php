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
         * @throws \ReflectionException
         */
        function getDependencies () {
            if (!isset($this->dependencies)) {
                $this->dependencies = $this->collectDependencies();
            }

            return $this->dependencies;
        }

        /**
         * @return DependencyContainer
         * @throws \ReflectionException
         */
        private function collectDependencies () {
            if (!$this->invokableReflection) {
                return new DependencyContainer();
            }

            $dependencies = [];
            foreach ($this->invokableReflection->getParameters() as $parameter) {
                $dependencies[] = Dependency::createFromReflectionParameter($parameter);
            }

            return new DependencyContainer(...$dependencies);
        }
    }