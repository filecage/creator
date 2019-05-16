<?php

    namespace Creator\Tests;

    use Creator\Creator;
    use Creator\Tests\Mocks\ArbitraryClassOnlyResolvableByFactory;
    use Creator\Tests\Mocks\ArbitraryFactory;
    use Creator\Tests\Mocks\ArbitraryInterface;
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

        function testExpectsInterfaceFactoryResultCaching () {
            $creator = new Creator();

            $creator->registerFactory(function() {
                return new ArbitraryClassOnlyResolvableByFactory(new SimpleClass(), ArbitraryFactory::PRIMITIVE_VALUE);
            }, ArbitraryInterface::class);

            $firstArbitraryImplementation = $creator->create(ArbitraryInterface::class);
            $secondArbitraryImplementation = $creator->create(ArbitraryInterface::class);

            $this->assertInstanceOf(ArbitraryInterface::class, $firstArbitraryImplementation);
            $this->assertSame($firstArbitraryImplementation, $secondArbitraryImplementation);
        }

        function testExpectsFactoryResultResultCachingForAnonymousResults () {
            $creator = new Creator();

            $creator->registerFactory(function() {
                return new class (new SimpleClass(), ArbitraryFactory::PRIMITIVE_VALUE) extends ArbitraryClassOnlyResolvableByFactory {};
            }, ArbitraryClassOnlyResolvableByFactory::class);

            $firstArbitraryImplementation = $creator->create(ArbitraryClassOnlyResolvableByFactory::class);
            $secondArbitraryImplementation = $creator->create(ArbitraryClassOnlyResolvableByFactory::class);

            $this->assertInstanceOf(ArbitraryClassOnlyResolvableByFactory::class, $firstArbitraryImplementation);
            $this->assertSame($firstArbitraryImplementation, $secondArbitraryImplementation);
        }

    }