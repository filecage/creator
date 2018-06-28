<?php

    namespace Creator\Exceptions;

    class InvalidFactoryException extends CreatorException {

        /**
         * @var string
         */
        protected $actualType;

        /**
         * @var string
         */
        protected $class;

        /**
         * @param mixed $actualFactory
         * @param string $class
         *
         * @return InvalidFactoryException
         */
        static function createWithUnknownActualType ($actualFactory, $class) {
            if (class_exists($actualFactory, false)) {
                $actualType = sprintf("class '%s'", $actualFactory);
            } elseif (is_object($actualFactory)) {
                $actualType = sprintf("instance of '%s'", get_class($actualFactory));
            } else {
                $actualType = gettype($actualFactory);
            }

            return new InvalidFactoryException($actualType, $class);
        }

        /**
         * @param string $actualType
         * @param string $class
         */
        function __construct ($actualType, $class) {
            $this->actualType = $actualType;
            $this->class = $class;

            parent::__construct(sprintf('Trying to register unsupported factory type "%s" for class "%s"', $actualType, $class));
        }
    }