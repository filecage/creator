<?php

    namespace Creator\Tests;

    use Creator\Creator;
    use Creator\Exceptions\InvalidFactoryException;
    use Creator\ResourceRegistry;
    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\MoreExtendedClass;
    use Creator\Tests\Mocks\SimpleClass;

    class FactoryTest extends AbstractCreatorTest {

        function testExpectsFactoryCallableToBeCalledForCreation () {
            $creator = new Creator();

            $simpleClass = new SimpleClass();
            $creator->registerFactory(ExtendedClass::class, function() use ($simpleClass) {
                return new ExtendedClass($simpleClass);
            });

            /** @var ExtendedClass $extendedClass */
            $extendedClass = $creator->create(ExtendedClass::class);

            $this->assertInstanceOf(ExtendedClass::class, $extendedClass);
            $this->assertSame($simpleClass, $extendedClass->getSimpleClass());
        }

        function testExpectsInjectedInstanceOverFactoryCreation () {
            $creator = new Creator();

            $uninjectedSimpleClass = new SimpleClass();
            $creator->registerFactory(ExtendedClass::class, function() use ($uninjectedSimpleClass) {
                return new ExtendedClass($uninjectedSimpleClass);
            });

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

            $creator->registerFactory(ExtendedClass::class, function (SimpleClass $simpleClass) {
                return new ExtendedClass($simpleClass);
            });

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

            $creator->registerFactory(MoreExtendedClass::class, function (Creator $creator) use ($simpleClass) {
                return $creator->createInjected(MoreExtendedClass::class)
                    ->with($simpleClass);
            });
        }

        function testExpectsInvalidactoryException () {
            $this->expectException(InvalidFactoryException::class);
            $this->expectExceptionMessageRegExp('/Trying to register unsupported factory type ".+" for class ".+"/');

            $this->creator->registerFactory(SimpleClass::class, null);
        }

    }