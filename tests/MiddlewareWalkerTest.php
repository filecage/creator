<?php

    namespace Creator\Tests;

    use Creator\MiddlewareWalker;
    use Creator\Tests\Middlewares\Rot13Middleware;
    use Creator\Tests\Middlewares\StringReverseMiddleware;

    class MiddlewareWalkerTest extends AbstractCreatorTest {

        function testExpectsMiddlewareInvocation () {
            $middlewareWalker = new MiddlewareWalker();
            $middlewareWalker->addMiddleware(new Rot13Middleware());

            $this->assertSame('Sbbone', $middlewareWalker->walkMiddlewares('Foobar'));
        }

        function testExpectsDeterministicMiddlewareOrder () {
            $middlewareWalker = new MiddlewareWalker();
            $middlewareWalker->addMiddleware(new Rot13Middleware())
                ->addMiddleware(new StringReverseMiddleware());

            $this->assertSame('enobbS', $middlewareWalker->walkMiddlewares('Foobar'));
        }

    }