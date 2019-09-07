<?php

    namespace Creator;

    class InvokableMethod extends Invokable {

        /**
         * @var object
         */
        private $thisContext;

        /**
         * @param \ReflectionFunctionAbstract $invokableReflection
         * @param object $thisContext
         */
        function __construct(\ReflectionFunctionAbstract $invokableReflection = null, $thisContext = null) {
            $this->thisContext = $thisContext;
            parent::__construct($invokableReflection);
        }

        /**
         * @return string
         */
        function getName () : string {
            return $this->invokableReflection->getDeclaringClass()->getName() . '::' . $this->invokableReflection->getName() . '()';
        }

        /**
         * @param array $args
         *
         * @return mixed|null
         */
        function invoke (array $args = []) {
            if (!$this->invokableReflection) {
                return null;
            }

            if (!$args) {
                return $this->invokableReflection->invoke($this->thisContext);
            } else {
                return $this->invokableReflection->invokeArgs($this->thisContext, $args);
            }
        }
    }