<?php

    namespace Creator\Interfaces;

    use Creator\Creatable;
    use Creator\ResourceRegistry;

    interface Middleware {

        function __invoke($buffer, callable $next);

    }