<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\MoreExtendedClassWithSingleDepedency;
    use Creator\Tests\Mocks\SimpleClass;

    class DependencyTreeResolutionTest extends AbstractCreatorTest {

        /**
         * @see https://github.com/filecage/creator/issues/1
         */
        function testExpectsNewInstanceWhenInnerDependencyHasInjectedDependency () {
            $simpleClass = new SimpleClass();
            $extendedClass = new ExtendedClass(new SimpleClass());

            $this->creator->registerClassResource($extendedClass);


            /** @var MoreExtendedClassWithSingleDepedency $moreExtendedClass */
            $moreExtendedClass = $this->creator->create(MoreExtendedClassWithSingleDepedency::class);

            /** @var MoreExtendedClassWithSingleDepedency $moreExtendedClassWithInjectedSimpleClass */
            $moreExtendedClassWithInjectedSimpleClass = $this->creator->createInjected(MoreExtendedClassWithSingleDepedency::class)->with($simpleClass)->create();

            $this->assertNotSame($moreExtendedClass, $moreExtendedClassWithInjectedSimpleClass);
            $this->assertSame($extendedClass, $moreExtendedClass->getExtendedClass());
            $this->assertNotSame($extendedClass, $moreExtendedClassWithInjectedSimpleClass->getExtendedClass());
            $this->assertSame($simpleClass, $moreExtendedClassWithInjectedSimpleClass->getExtendedClass()->getSimpleClass());
            $this->assertSame($simpleClass, $moreExtendedClassWithInjectedSimpleClass->getExtendedClass()->getSimpleClass());
        }

    }