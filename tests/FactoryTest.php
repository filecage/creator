<?php

    namespace Creator\Tests;

    use Creator\Creator;
    use Creator\Exceptions\InvalidFactoryException;
    use Creator\ResourceRegistry;
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
                    ->with($simpleClass);
            }, MoreExtendedClass::class);
        }

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

    }