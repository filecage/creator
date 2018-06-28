<?php

    namespace Creator;

    use Creator\Exceptions\InvalidFactoryException;
    use Creator\Interfaces\Factory;

    abstract class InvokableFactory extends Invokable {

        static function createFromAnyFactory ($factory) {
            if (is_callable($factory)) {
                $invokable = InvokableClosure::createFromCallable($factory);
            } elseif ($factory instanceof Factory) {
                $invokable = InvokableClosure::createFromCallable([$factory, 'createInstance']);
            } else {
                throw InvalidFactoryException::createWithUnknownActualType($factory);
            }

            return $invokable;
        }

    }