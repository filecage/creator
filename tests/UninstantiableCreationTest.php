<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\ExtendedInterface;
    use Creator\Tests\Mocks\SimpleAbstractClass;
    use Creator\Tests\Mocks\SimpleClass;
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
            $this->assertInstanceOf(ExtendedClass::class, $this->creator->create(ExtendedInterface::class));
        }

        function testExpectsFactoryInstanceCreatedWithInjectedInstance () {
            $simpleInstance = new SimpleClass();
            /** @var ExtendedInterface $extendedInstance */
            $extendedInstance = $this->creator->createInjected(ExtendedInterface::class)
                ->with($simpleInstance)
                ->create();

            $this->assertInstanceOf(ExtendedInterface::class, $extendedInstance);
            $this->assertInstanceOf(ExtendedClass::class, $extendedInstance);
            $this->assertSame($simpleInstance, $extendedInstance->getSimpleClass());
        }

    }