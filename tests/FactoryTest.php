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

        function testExpectsInjectedInstanceOverFactoryCreation () {
            $creator = new Creator();

            $uninjectedSimpleClass = new SimpleClass();
            $creator->registerFactory(function() use ($uninjectedSimpleClass) {
                return new ExtendedClass($uninjectedSimpleClass);
            }, ExtendedClass::class);

            $injectedSimpleClass = new SimpleClass();

            /** @var ExtendedClass $injectedExtendedClass  */
            $injectedExtendedClass = $creator->createInjected(ExtendedClass::class)->with($injectedSimpleClass)->create();
            $uninjectedExtendedClass = $creator->create(ExtendedClass::class);

            $this->assertInstanceOf(ExtendedClass::class, $injectedExtendedClass);
            $this->assertInstanceOf(ExtendedClass::class, $uninjectedExtendedClass);
            $this->assertSame($injectedSimpleClass, $injectedExtendedClass->getSimpleClass());
            $this->assertSame($uninjectedSimpleClass, $uninjectedExtendedClass->getSimpleClass());
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

        function testExpectsInjectedFactoryOverGlobalFactory () {
            $creator = new Creator();

            $globalSimpleClass = new SimpleClass();
            $extendedFactory = new ExtendedInterfaceFactory($globalSimpleClass);
            $creator->registerFactory($extendedFactory, ExtendedClass::class);

            $injectedSimpleClass = new SimpleClass();

            /** @var ExtendedClass $injectedExtendedClass */
            $injectedExtendedClass = $creator->createInjected(ExtendedClass::class)
                ->withFactory(function() use ($injectedSimpleClass){
                    return new ExtendedClass($injectedSimpleClass);
                }, ExtendedClass::class)->create();

            /** @var ExtendedClass $globalExtendedClass */
            $globalExtendedClass = $creator->create(ExtendedClass::class);

            $this->assertInstanceOf(ExtendedClass::class, $injectedExtendedClass);
            $this->assertInstanceOf(ExtendedClass::class, $globalExtendedClass);
            $this->assertSame($injectedSimpleClass, $injectedExtendedClass->getSimpleClass());
            $this->assertSame($globalSimpleClass, $globalExtendedClass->getSimpleClass());
        }

        function testExpectsInvalidFactoryExceptionInGlobalRegistry () {
            $this->expectException(InvalidFactoryException::class);
            $this->expectExceptionMessageRegExp('/Trying to register unsupported factory type ".+" for class ".+"/');

            $this->creator->registerFactory(null, SimpleClass::class);
        }

        function testExpectsInvalidFactoryExceptionInInjectedRegistry () {
            $this->expectException(InvalidFactoryException::class);
            $this->expectExceptionMessageRegExp('/Trying to register unsupported factory type ".+" for class ".+"/');

            $this->creator->createInjected(SimpleClass::class)
                ->withFactory(null, SimpleClass::class);
        }

    }