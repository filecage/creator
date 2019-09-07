<?php

    namespace Creator\Interfaces;

    interface SelfFactory {

        /**
         * This method can return a closure or callable that defines dependencies and a self-creation process for
         * a class resource. By using the SelfFactory, it is possible to take advantage of a convenient auto-wiring
         * without ignoring the best practices of dependency injection, especially when working with third-party
         * libraries where you do not have control over the classes constructors.
         *
         * @see \Creator\Tests\Mocks\SelfFactory\SelfFactorizingClass for example usage
         *
         * Creator will throw an InvalidFactoryResult exception if the return of the callable is not
         * an instance of the class.
         *
         * @return callable
         */
        static function createSelf () : callable;

    }