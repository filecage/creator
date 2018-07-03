<?php

    namespace Creator\Tests;

    use Creator\Creator;
    use Creator\ResourceRegistry;
    use Creator\Tests\Mocks\ArbitraryClassOnlyResolvableByFactory;
    use Creator\Tests\Mocks\ArbitraryFactory;
    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\ExtendedInterfaceFactory;
    use Creator\Tests\Mocks\MoreExtendedClass;
    use Creator\Tests\Mocks\SimpleClass;

    class FactoryTest extends AbstractCreatorTest {

        function testExpectsFactoryCallableToBeCalledForCreation () {
            $creator = new Creator();

            $simpleClass = new SimpleClass();
            $creator->registerFactory( function() use ($simpleClass) {
                return new ExtendedClass($simpleClass);
            }, ExtendedClass::class);

            /** @var ExtendedClass $extendedClass */
            $extendedClass = $creator->create(ExtendedClass::class);

            $this->assertInstanceOf(ExtendedClass::class, $extendedClass);
            $this->assertSame($simpleClass, $extendedClass->getSimpleClass());
        }

        function testExpectsFactoryClassToBeCalledForCreation () {
            $creator = new Creator();

            $simpleClass = new SimpleClass();
            $extendedFactory = new ExtendedInterfaceFactory($simpleClass);
            $creator->registerFactory($extendedFactory, ExtendedClass::class);

            /** @var ExtendedClass $extendedClass */
            $extendedClass = $creator->create(ExtendedClass::class);

            $this->assertInstanceOf(ExtendedClass::class, $extendedClass);
            $this->assertSame($simpleClass, $extendedClass->getSimpleClass());
        }

        function testExpectsFactoryCallableWithDependencyToBeCalledForCreation () {
            $simpleClass = new SimpleClass();

            $registry = (new ResourceRegistry())->registerClassResource($simpleClass);
            $creator = $this->getWithRegistry($registry);

            $creator->registerFactory(function (SimpleClass $simpleClass) {
                return new ExtendedClass($simpleClass);
            }, ExtendedClass::class);

            /** @var ExtendedClass $extendedClass */
            $extendedClass = $creator->create(ExtendedClass::class);

            $this->assertInstanceOf(ExtendedClass::class, $extendedClass);
            $this->assertSame($simpleClass, $extendedClass->getSimpleClass());
        }

        function testExpectsFactoryCallableWithInjectedCreation () {
            $registry = new ResourceRegistry();
            $creator = $this->getWithRegistry($registry);

            $registry->registerClassResource($creator);
            $simpleClass = new SimpleClass();

            $creator->registerFactory(function (Creator $creator) use ($simpleClass) {
                return $creator->createInjected(MoreExtendedClass::class)
                    ->with($simpleClass)
                    ->create();
            }, MoreExtendedClass::class);

            /** @var MoreExtendedClass $moreExtendedClass */
            $moreExtendedClass = $creator->create(MoreExtendedClass::class);

            $this->assertInstanceOf(MoreExtendedClass::class, $moreExtendedClass);
            $this->assertSame($simpleClass, $moreExtendedClass->getSimpleClass());
        }

        function testExpectsLazyFactoryCreation () {
            $creator = new Creator();

            $creator->registerFactory(ArbitraryFactory::class, ArbitraryClassOnlyResolvableByFactory::class);

            /** @var ArbitraryClassOnlyResolvableByFactory $arbitraryClass */
            $arbitraryClass = $creator->create(ArbitraryClassOnlyResolvableByFactory::class);

            $this->assertInstanceOf(ArbitraryClassOnlyResolvableByFactory::class, $arbitraryClass);
            $this->assertSame(ArbitraryFactory::PRIMITIVE_VALUE, $arbitraryClass->getPrimitiveValue());
        }

        function testExpectsCachedFactoryIfInjectedFactoryIsBoundLazy () {
            $resourceRegistry = new ResourceRegistry();
            $simpleClass = new SimpleClass();

            $creator = $this->getWithRegistry($resourceRegistry);
            $resourceRegistry->registerClassResource(new ArbitraryFactory($simpleClass, ArbitraryFactory::ANOTHER_PRIMITIVE_VALUE));

            /** @var ArbitraryClassOnlyResolvableByFactory $arbitraryClass */
            $arbitraryClass = $creator->createInjected(ArbitraryClassOnlyResolvableByFactory::class)
                ->withFactory(ArbitraryFactory::class, ArbitraryClassOnlyResolvableByFactory::class)
                ->create();

            // assertions ensure https://github.com/filecage/creator/issues/5#issuecomment-401415849
            $this->assertInstanceOf(ArbitraryClassOnlyResolvableByFactory::class, $arbitraryClass);
            $this->assertSame(ArbitraryFactory::ANOTHER_PRIMITIVE_VALUE, $arbitraryClass->getPrimitiveValue());
            $this->assertSame($simpleClass, $arbitraryClass->getSimpleClass());
        }

        function testShouldCreateNewFactoryIfFactoryDependencyIsInjected () {
            $resourceRegistry = new ResourceRegistry();
            $injectedSimpleClass = new SimpleClass();
            $globalSimpleClass = new SimpleClass();

            $creator = $this->getWithRegistry($resourceRegistry);
            $creator
                ->registerClassResource(new ArbitraryFactory($globalSimpleClass))
                ->registerFactory(ArbitraryFactory::class, ArbitraryClassOnlyResolvableByFactory::class);

            /** @var ArbitraryClassOnlyResolvableByFactory $injectedArbitraryClass */
            $injectedArbitraryClass = $creator->createInjected(ArbitraryClassOnlyResolvableByFactory::class)
                ->with($injectedSimpleClass)
                ->create();

            /** @var ArbitraryClassOnlyResolvableByFactory $globalArbitraryClass */
            $globalArbitraryClass = $creator->create(ArbitraryClassOnlyResolvableByFactory::class);

            $this->assertInstanceOf(ArbitraryClassOnlyResolvableByFactory::class, $injectedArbitraryClass);
            $this->assertSame($globalSimpleClass, $globalArbitraryClass->getSimpleClass());
            $this->assertSame($injectedSimpleClass, $injectedArbitraryClass->getSimpleClass());
        }


    }