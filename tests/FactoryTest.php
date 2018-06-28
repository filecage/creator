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

            $firstSimpleClass = new SimpleClass();
            $creator->registerFactory(ExtendedClass::class, function() use ($firstSimpleClass) {
                return new ExtendedClass($firstSimpleClass);
            });

            $secondSimpleClass = new SimpleClass();

            /** @var ExtendedClass $extendedClass */
            $extendedClass = $creator->createInjected(ExtendedClass::class)->with($secondSimpleClass)->create();

            $this->assertInstanceOf(ExtendedClass::class, $extendedClass);
            $this->assertSame($secondSimpleClass, $extendedClass->getSimpleClass());
        }

        function testExpects

    }