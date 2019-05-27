<?php

    namespace Creator\Exceptions;

    class Unreflectable extends Unresolvable {

        /**
         * @param string $classResourceKey
         * @param string $message
         */
        function __construct (string $classResourceKey, string $message) {
            parent::__construct("Class `{$classResourceKey}` can not be reflected: {$message}", null);
        }

    }