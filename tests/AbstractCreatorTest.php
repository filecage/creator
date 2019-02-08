<?php

    namespace Creator\Tests;

    use Creator\Creator;
    use Creator\ResourceRegistry;
    use PHPUnit\Framework\TestCase;

    abstract class AbstractCreatorTest extends TestCase {

        /**
         * @var Creator
         */
        protected $creator;

        function setUp () : void {
            $this->creator = new Creator();
        }

        /**
         * @param ResourceRegistry $resourceRegistry
         * @return Creator
         */
        function getWithRegistry (ResourceRegistry $resourceRegistry) {
            return new Creator($resourceRegistry);
        }

    }