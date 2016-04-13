<?php

    namespace Creator\Tests;

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

    }