<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\AnotherSimpleClass;
    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\MoreExtendedClass;
    use Creator\Tests\Mocks\SimpleClass;

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

    }