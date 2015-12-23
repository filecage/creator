<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\SimpleAbstractClass;
    use Creator\Tests\Mocks\SimpleInterface;
    use Creator\Tests\Mocks\SimpleSingleton;

    class UninstantiableCreationTest extends AbstractCreatorTest {

        function testExpectsInstanceFromSingleton () {
            $this->assertInstanceOf(SimpleSingleton::class, $this->creator->create(SimpleSingleton::class));
        }

        function testExpectsInstanceFromUninstantiableInterface () {
            $this->assertInstanceOf(SimpleInterface::class, $this->creator->create(SimpleInterface::class));
        }

        function testExpectsInstanceFromUninstantiableAbstractClass () {
            $this->assertInstanceOf(SimpleAbstractClass::class, $this->creator->create(SimpleAbstractClass::class));
        }

    }