<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\MoreExtendedClass;
    use Creator\Tests\Mocks\MoreExtendedInterface;
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

        function testExpectsInstanceFromFactoryWithDependencies () {
            $this->assertInstanceOf(MoreExtendedClass::class, $this->creator->create(MoreExtendedInterface::class));
        }

    }