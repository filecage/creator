<?php

    namespace Creator\Exceptions;

    class InvalidFactory extends CreatorException {

        /**
         * @var string
         */
        protected $actualType;

        /**
         * @var string
         */
        protected $class;

        /**
         * @var string|null
         */
        private $additionalMessage;

        /**
         * @param mixed $actualFactory
         * @param string $class
         *
         * @return InvalidFactory
         */
        static function createWithUnknownActualType ($actualFactory, $class = null) {
            if (is_string($actualFactory) && class_exists($actualFactory, false)) {
                $actualType = sprintf("class `%s`", $actualFactory);
            } elseif (is_object($actualFactory)) {
                $actualType = sprintf("instance of `%s`", get_class($actualFactory));
            } else {
                $actualType = gettype($actualFactory);
            }

            return new InvalidFactory($actualType, $class);
        }

        /**
         * @param string $actualType
         * @param string $class
         */
        function __construct ($actualType, $class = null, string $additionalMessage = null) {
            $this->actualType = $actualType;
            $this->class = $class;
            $this->additionalMessage = $additionalMessage;

            parent::__construct($this->createMessage($additionalMessage));
        }

        /**
         * @param $class
         *
         * @return InvalidFactory
         * @throws CreatorException
         */
        function enrichClass ($class) {
            if ($this->class) {
                throw new CreatorException('Fatal when enriching class name of InvalidFactoryException: Exceptions may only be enriched once');
            }

            $this->class = $class;
            $this->message = $this->createMessage($this->additionalMessage);

            return $this;
        }

        /**
         * @return string
         */
        private function createMessage (?string $additionalMessage = null) {
            $message = sprintf('Trying to register unsupported factory type `%s` for class `%s`', $this->actualType, $this->class);
            if ($additionalMessage !== null) {
                $message .= " ({$additionalMessage})";
            }

            return $message;
        }
    }