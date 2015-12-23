<?php

    namespace Creator\Tests;

    use Creator\Creator;

    abstract class AbstractCreatorTest extends \PHPUnit_Framework_TestCase {

        /**
         * @var Creator
         */
        protected $creator;

        function setUp () {
            $this->creator = new Creator();
        }

    }