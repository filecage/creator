<?php

    namespace Creator\Tests;

    use Creator\MiddlewareWalkerTrait;
    use Creator\Tests\Middlewares\ReverseRot13Middleware;
    use Creator\Tests\Middlewares\ReverseStringReverseMiddleware;
    use Creator\Tests\Middlewares\Rot13Middleware;
    use Creator\Tests\Middlewares\StringReverseMiddleware;

    class MiddlewareWalkerTest extends AbstractCreatorTest {

        /**
         * @return MiddlewareWalkerTrait
         */
        function getMiddlewareWalker () {
            /** @var MiddlewareWalkerTrait $ghotie */
            $ghotie = new class {
                use MiddlewareWalkerTrait;

                function run ($buffer) {
                    return $this->walkMiddlewares($buffer);
                }
            };

            return $ghotie; // using a ghotie to fix intelij warnings
        }

        function testExpectsMiddlewareInvocation () {
            $middlewareWalker = $this->getMiddlewareWalker()->addMiddleware(new Rot13Middleware());

            $this->assertSame('Sbbone', $middlewareWalker->run('Foobar'));
        }

        function testExpectsDeterministicMiddlewareOrder () {
            $middlewareWalker = $this->getMiddlewareWalker()
                ->addMiddleware(new Rot13Middleware())
                ->addMiddleware(new StringReverseMiddleware());

            $this->assertSame('enobbS', $middlewareWalker->run('Foobar'));
        }

        function testExpectsRuntimeOrderDefinedByMiddleware () {
            $middlewareWalker = $this->getMiddlewareWalker()
                ->addMiddleware(new ReverseRot13Middleware())
                ->addMiddleware(new ReverseStringReverseMiddleware())
                ->addMiddleware(new Rot13Middleware())
                ->addMiddleware(new StringReverseMiddleware());

            $this->assertSame('Foobar', $middlewareWalker->run('Foobar'));
        }

    }