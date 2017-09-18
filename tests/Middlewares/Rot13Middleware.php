<?php

    namespace Creator\Tests\Middlewares;

    use Creator\Interfaces\Middleware;

    class Rot13Middleware implements Middleware {

        function __invoke ($buffer, callable $next) {
            return $next(str_rot13($buffer));
        }

    }