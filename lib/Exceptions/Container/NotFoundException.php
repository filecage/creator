<?php

    namespace Creator\Exceptions\Container;

    use Creator\Exceptions\CreatorException;
    use Creator\Exceptions\Unreflectable;
    use Psr\Container\NotFoundExceptionInterface;

    class NotFoundException extends CreatorException implements NotFoundExceptionInterface {

        /**
         * @param Unreflectable $unresolvable
         * @return NotFoundException
         */
        static function createFromUnreflectable (Unreflectable $unresolvable) {
            return new static($unresolvable->getMessage(), 0, $unresolvable);
        }

    }