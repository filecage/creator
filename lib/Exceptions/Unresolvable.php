<?php

    namespace Creator\Exceptions;

    class Unresolvable extends \Exception {

        /**
         * @var string
         */
        protected $message;

        /**
         * @var string
         */
        protected $class;

        /**
         * @param string $message
         * @param string $class
         */
        function __construct ($message, $class = null) {
            $this->message = $message;
            $this->class = $class;

            parent::__construct($this->getFullMessage());
        }

        /**
         * @return string
         */
        function getFullMessage () {
            return ($this->class) ? sprintf('%s when creating %s', $this->message, $this->class) : $this->message;
        }

    }