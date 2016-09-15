<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\AnotherSimpleClass;
    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\MoreExtendedClass;
    use Creator\Tests\Mocks\SimpleClass;
    use Creator\Tests\Mocks\SimpleClassWithPrimitiveDependencies;

    class InjectedCreationTest extends AbstractCreatorTest {

        function testExpectsInjectedInstance () {
            $simpleInstance = new SimpleClass();

            /** @var ExtendedClass $extendedInstance */
            $extendedInstance = $this->creator->createInjected(ExtendedClass::class)
                ->with($simpleInstance)
                ->create();

            $this->assertSame($simpleInstance, $extendedInstance->getSimpleClass());
        }

        function testExpectsOtherInstancesFromRegistry () {
            $simpleInstance = new SimpleClass();
            $anotherSimpleInstance = new AnotherSimpleClass();

            $this->creator->registerClassResource($simpleInstance);

            /** @var MoreExtendedClass $extendedInstance */
            $extendedInstance = $this->creator->createInjected(MoreExtendedClass::class)
                ->with($anotherSimpleInstance)
                ->create();

            $this->assertSame($simpleInstance, $extendedInstance->getSimpleClass());
            $this->assertSame($simpleInstance, $extendedInstance->getExtendedClass()->getSimpleClass());
            $this->assertSame($anotherSimpleInstance, $extendedInstance->getAnotherSimpleClass());
        }

        function testExpectsInjectedInstanceInAllResolvedDependencies () {
            $simpleInstance = new SimpleClass();

            /** @var MoreExtendedClass $moreExtendedInstance */
            $moreExtendedInstance = $this->creator->createInjected(MoreExtendedClass::class)
                ->with($simpleInstance)
                ->create();

            $this->assertSame($simpleInstance, $moreExtendedInstance->getSimpleClass());
            $this->assertSame($simpleInstance, $moreExtendedInstance->getExtendedClass()->getSimpleClass());
        }

        function testExpectsInjectedInstanceOverRegisteredInstance () {
            $simpleInstance = new SimpleClass();
            $secondSimpleInstance = new SimpleClass();

            $this->creator->registerClassResource($secondSimpleInstance);

            /** @var ExtendedClass $extendedInstance */
            $extendedInstance = $this->creator->createInjected(ExtendedClass::class)
                ->with($simpleInstance)
                ->create();

            $this->assertSame($simpleInstance, $extendedInstance->getSimpleClass());
        }

        function testExpectsDifferentInstancesWhenUsingInjectedAndDefaultCreation () {
            $simpleInstance = new SimpleClass();

            // Right order is important, first class would be registered to registry
            /** @var ExtendedClass $extendedInstance */
            $extendedInstance = $this->creator->createInjected(ExtendedClass::class)
                ->with($simpleInstance)
                ->create();

            /** @var ExtendedClass $secondExtendedInstance */
            $secondExtendedInstance = $this->creator->create(ExtendedClass::class);

            /** @var ExtendedClass $thirdExtendedInstance */
            $thirdExtendedInstance = $this->creator->createInjected(ExtendedClass::class)
                ->with($simpleInstance)
                ->create();

            $this->assertSame($simpleInstance, $extendedInstance->getSimpleClass());
            $this->assertNotSame($extendedInstance, $secondExtendedInstance);
            $this->assertNotSame($simpleInstance, $secondExtendedInstance->getSimpleClass());
            $this->assertNotSame($extendedInstance, $thirdExtendedInstance);
            $this->assertNotSame($secondExtendedInstance, $thirdExtendedInstance);
        }

        function testExpectsPrimitiveResourceFromInjectedRegistry () {
            /** @var SimpleClassWithPrimitiveDependencies $simpleInstance */
            $simpleInstance = $this->creator->createInjected(SimpleClassWithPrimitiveDependencies::class)
                ->with(SimpleClassWithPrimitiveDependencies::FROM_REGISTRY, 'fromRegistry')
                ->create();

            $this->assertInstanceOf(SimpleClassWithPrimitiveDependencies::class, $simpleInstance);
            $this->assertSame(SimpleClassWithPrimitiveDependencies::FROM_REGISTRY, $simpleInstance->getFromRegistry());
        }

        function testExpectsPrimitiveResourceFromInjectedRegistryOverGlobalRegistry () {
            $this->creator->registerPrimitiveResource('fromRegistry', 'global');

            /** @var SimpleClassWithPrimitiveDependencies $simpleInstance */
            $simpleInstance = $this->creator->createInjected(SimpleClassWithPrimitiveDependencies::class)
                ->with('injected', 'fromRegistry')
                ->create();

            $this->assertInstanceOf(SimpleClassWithPrimitiveDependencies::class, $simpleInstance);
            $this->assertSame('injected', $simpleInstance->getFromRegistry());
        }

    }