<?php

    namespace Creator\Tests\Middlewares;

    use Creator\Interfaces\Middleware;

    class ReverseStringReverseMiddleware implements Middleware {

        /**
         * @param string $buffer
         * @param callable $next
         * @return mixed
         */
        function __invoke ($buffer, callable $next) {
            return implode('', array_reverse(str_split($next($buffer))));
        }

    }