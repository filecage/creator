<?php

    namespace Creator\Exceptions\Container;

    use Creator\Exceptions\CreatorException;
    use Creator\Exceptions\Unresolvable;
    use Psr\Container\ContainerExceptionInterface;

    class ContainerException extends CreatorException implements ContainerExceptionInterface {

        /**
         * @param Unresolvable $unresolvable
         * @return ContainerExceptionInterface
         */
        static function createFromUnresolvable (Unresolvable $unresolvable) {
            return new static($unresolvable->getMessage(), 0, $unresolvable);
        }
    }