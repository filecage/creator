<?php

    namespace Creator;

    class ClassResource {

        /**
         * @var object
         */
        private $instance;

        /**
         * @var \ReflectionClass
         */
        private $reflection;

        /**
         * @param object $instance
         * @return ClassResource
         */
        static function createFromInstance ($instance) : ClassResource {
            return new static($instance, new \ReflectionClass($instance));
        }

        /**
         * @param object $instance
         * @param \ReflectionClass $reflection
         */
        function __construct ($instance, \ReflectionClass $reflection) {
            $this->instance = $instance;
            $this->reflection = $reflection;
        }

        /**
         * @return object
         */
        function getInstance () {
            return $this->instance;
        }

        /**
         * @return \ReflectionClass
         */
        function getReflection () : \ReflectionClass {
            return $this->reflection;
        }

        /**
         * @param string $interface
         * @return bool
         */
        function implementsInterface (string $interface) : bool {
            return $this->reflection->implementsInterface($interface);
        }

    }