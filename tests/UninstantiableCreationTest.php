<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\SimpleSingleton;

    class UninstantiableCreationTest extends AbstractCreatorTest {

        function testExpectsInstanceFromSingleton () {
            $this->assertInstanceOf(SimpleSingleton::class, $this->creator->create(SimpleSingleton::class));
        }

    }