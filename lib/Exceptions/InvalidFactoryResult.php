<?php

    namespace Creator\Exceptions;

    class InvalidFactoryResult extends CreatorException {

        /**
         * @param string $factoryClassName
         * @param string|null $expectedClassName
         * @param mixed $actual
         */
        function __construct (string $factoryClassName, ?string $expectedClassName, $actual) {
            if (is_object($actual)) {
                $actualType = sprintf('instance of `%s`', get_class($actual));
            } else {
                $actualType = sprintf('`%s`', gettype($actual));
            }

            if ($expectedClassName === null) {
                parent::__construct("SelfFactory `{$factoryClassName}` returned {$actualType}, expected instance of self instead");
            } else {
                parent::__construct("Factory `{$factoryClassName}` returned {$actualType}, expected instance of `{$expectedClassName}` instead");
            }
        }

    }