<?php

    namespace Creator\Tests\Middlewares;

    use Creator\Interfaces\Middleware;

    class ReverseRot13Middleware implements Middleware {

        function __invoke ($buffer, callable $next) {
            return str_rot13($next($buffer));
        }

    }