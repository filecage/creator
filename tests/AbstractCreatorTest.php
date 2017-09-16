<?php

    namespace Creator\Tests;

    use Creator\Creator;
    use PHPUnit\Framework\TestCase;

    abstract class AbstractCreatorTest extends TestCase {

        /**
         * @var Creator
         */
        protected $creator;

        function setUp () {
            $this->creator = new Creator();
        }

    }