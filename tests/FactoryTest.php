<?php

    namespace Creator\Tests;

    use Creator\Creator;
    use Creator\Tests\Mocks\ExtendedClass;
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


        function testExpectsInvalidactoryException () {
            $this->expectException(InvalidFactoryException::class);
            $this->expectExceptionMessageRegExp('/Trying to register unsupported factory type ".+" for class ".+"/');

            $this->creator->registerFactory(SimpleClass::class, null);
        }

    }