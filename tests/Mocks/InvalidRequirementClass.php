<?php

    namespace Creator\Tests\Mocks;

    class InvalidRequirementClass {

        function __construct(\InexistentClass $inexistent) {}

    }