<?php

    namespace Creator\Tests;

    use Creator\Creator;
    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\MoreExtendedClass;
    use Creator\Tests\Mocks\SimpleClass;

    class ResourceCachingTest extends AbstractCreatorTest {

        function testExpectsFactoryResultToBeCached () {
            $creator = new Creator();

            $simpleClass = new SimpleClass();
            $creator->registerFactory( function() use ($simpleClass) {
                return new ExtendedClass($simpleClass);
            }, ExtendedClass::class);

            $firstExtendedClass = $creator->create(ExtendedClass::class);
            $secondExtendedClass = $creator->create(ExtendedClass::class);

            $this->assertInstanceOf(ExtendedClass::class, $firstExtendedClass);
            $this->assertSame($firstExtendedClass, $secondExtendedClass);
        }

        function testExpectsInjectedFactoryResultNotToBeCached () {
            $creator = new Creator();
            $simpleClass = new SimpleClass();

            /** @var ExtendedClass $factoryExtendedClass */
            $factoryExtendedClass = $creator->createInjected(ExtendedClass::class)
                ->withFactory(function() use ($simpleClass) {
                    return new ExtendedClass($simpleClass);
                }, ExtendedClass::class)
                ->create();

            /** @var ExtendedClass $defaultExtendedClass */
            $defaultExtendedClass = $creator->create(ExtendedClass::class);

            $this->assertInstanceOf(ExtendedClass::class, $factoryExtendedClass);
            $this->assertNotSame($factoryExtendedClass, $defaultExtendedClass);
            $this->assertSame($simpleClass, $factoryExtendedClass->getSimpleClass());
            $this->assertNotSame($simpleClass, $defaultExtendedClass->getSimpleClass());
        }


        function testExpectsInnerDependencyCachingWhenForcingInstance () {
            $simpleInstance = new SimpleClass();
            $this->creator->registerClassResource($simpleInstance);

            $a = $this->creator->create(MoreExtendedClass::class, true);
            $b = $this->creator->create(MoreExtendedClass::class, true);

            $this->assertSame($simpleInstance, $a->getSimpleClass());
            $this->assertSame($simpleInstance, $b->getSimpleClass());
            $this->assertSame($a->getAnotherSimpleClass(), $b->getAnotherSimpleClass());
        }

    }