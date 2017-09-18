<?php

    namespace Creator\Tests\Middlewares;

    use Creator\Interfaces\Middleware;

    class StringReverseMiddleware implements Middleware {

        /**
         * @param string $buffer
         * @param callable $next
         * @return mixed
         */
        function __invoke ($buffer, callable $next) {
            return $next(implode('', array_reverse(str_split($buffer))));
        }

    }