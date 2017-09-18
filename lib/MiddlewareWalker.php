<?php

    namespace Creator;

    use Creator\Interfaces\Middleware;

    class MiddlewareWalker {

        /**
         * @var callable[]
         */
        private $middlewareStack = [];

        /**
         * @param mixed $buffer
         * @return mixed
         */
        function __invoke ($buffer) {
            return $buffer;
        }

        /**
         * @param Middleware $middleware
         * @return $this
         */
        function addMiddleware (Middleware $middleware) {
            $next = end($this->middlewareStack) ?: $this;
            $this->middlewareStack[] = function($result) use ($middleware, $next) {
                $return  = call_user_func($middleware, $result, $next);
                var_dump($return);

                return $return;
            };

            return $this;
        }

        function walkMiddlewares ($buffer = null) {
            $entryPoint = end($this->middlewareStack);

            return call_user_func($entryPoint, $buffer);
        }

    }