<?php

    namespace Creator;

    class InvokableMethod extends Invokable {

        /**
         * @var object
         */
        private $thisContext;

        /**
         * @param \ReflectionMethod $invokableReflection
         * @param object $thisContext
         */
        function __construct(\ReflectionMethod $invokableReflection = null, $thisContext = null) {
            $this->thisContext = $thisContext;
            parent::__construct($invokableReflection);
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