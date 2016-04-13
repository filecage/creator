<?php

    namespace Creator;

    class InvokableClosure extends Invokable {

        /**
         * @param callable $callable
         *
         * @return static
         */
        static function createFromCallable (callable $callable) {
            if (is_array($callable) && count($callable) === 2) {
                return new InvokableMethod(new \ReflectionMethod($callable[0], $callable[1]), $callable[0]);
            }

            return new static(new \ReflectionFunction($callable));
        }

        /**
         * @param \ReflectionFunction $invokableReflection
         */
        function __construct(\ReflectionFunction $invokableReflection) {
            parent::__construct($invokableReflection);
        }

        /**
         * @param array $args
         *
         * @return mixed
         */
        function invoke (array $args = []) {
            return (!$args) ? $this->invokableReflection->invoke() : $this->invokableReflection->invokeArgs($args);
        }
    }