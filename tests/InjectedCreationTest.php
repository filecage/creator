<?php

    namespace Creator\Tests;

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
            $this->creator->registerClassResource($simpleInstance);

            /** @var ExtendedClass $extendedInstance */
            $extendedInstance = $this->creator->createInjected(ExtendedClass::class)
                ->create();

            $this->assertSame($simpleInstance, $extendedInstance->getSimpleClass());
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

    }