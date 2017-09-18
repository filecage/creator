<?php

    namespace Creator\Tests;

    use Creator\MiddlewareWalkerTrait;
    use Creator\Tests\Middlewares\Rot13Middleware;
    use Creator\Tests\Middlewares\StringReverseMiddleware;

    class MiddlewareWalkerTest extends AbstractCreatorTest {

        /**
         * @return MiddlewareWalkerTrait
         */
        function getMiddlewareWalker () {
            return new class { use MiddlewareWalkerTrait; };
        }

        function testExpectsMiddlewareInvocation () {
            $middlewareWalker = $this->getMiddlewareWalker()->addMiddleware(new Rot13Middleware());

            $this->assertSame('Sbbone', $middlewareWalker->walkMiddlewares('Foobar'));
        }

        function testExpectsDeterministicMiddlewareOrder () {
            $middlewareWalker = $this->getMiddlewareWalker()
                ->addMiddleware(new Rot13Middleware())
                ->addMiddleware(new StringReverseMiddleware());

            $this->assertSame('enobbS', $middlewareWalker->walkMiddlewares('Foobar'));
        }

    }