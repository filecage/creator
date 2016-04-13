<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\AnotherSimpleClass;
    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\MoreExtendedClass;
    use Creator\Tests\Mocks\SimpleClass;

    class InvocationTest extends AbstractCreatorTest {

        function testExpectsInvocationWithRequiredParameters () {
            $that = $this;
            $this->creator->invoke(function(SimpleClass $simpleClass) use ($that) {
                $that->assertInstanceOf(SimpleClass::class, $simpleClass);
            });
        }

        function testExpectsInvocationWithInjectedInstance () {
            $that = $this;
            $injectionClass = new SimpleClass();

            $this->creator->invokeWith(function(SimpleClass $simpleClass) use ($that, $injectionClass){
                $that->assertSame($injectionClass, $simpleClass);
            })
                ->with($injectionClass)
                ->invoke();
        }

        function testExpectsInvocationInObjectContext () {
            $simpleClass = new SimpleClass();
            $extendedClass = new ExtendedClass($simpleClass);

            /** @var MoreExtendedClass $moreExtendedClass */
            $moreExtendedClass = $this->creator->invoke([$extendedClass, 'getMoreExtendedClass']);

            $this->assertInstanceOf(MoreExtendedClass::class, $moreExtendedClass);
            $this->assertInstanceOf(AnotherSimpleClass::class, $moreExtendedClass->getAnotherSimpleClass());
            $this->assertSame($simpleClass, $moreExtendedClass->getSimpleClass());
            $this->assertSame($extendedClass, $moreExtendedClass->getExtendedClass());
        }

    }