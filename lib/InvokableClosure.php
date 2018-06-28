<?php

    namespace Creator;

    use Creator\Exceptions\Unresolvable;

    class InvokableClosure extends Invokable {

        /**
         * @param callable $callable
         *
         * @return Invokable
         * @throws Unresolvable
         */
        static function createFromCallable (callable $callable) : Invokable {
            if (is_array($callable) && count($callable) === 2) {
                // todo: Creator should resolve the object if we know the class name
                if (!is_object($callable[0])) {
                    throw new Unresolvable('Unable to handle invokation of object-context callable: no object given on callable index 0');
                }

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