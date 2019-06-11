<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\AnotherSimpleClass;
    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\MoreExtendedClassWithExtendedAndSimpleDependency;
    use Creator\Tests\Mocks\SimpleClass;

    class DependencyTreeResolutionTest extends AbstractCreatorTest {

        /**
         * @see https://github.com/filecage/creator/issues/1
         */
        function testExpectsNewInstanceWhenInnerDependencyHasInjectedDependency () {
            $simpleClass = new SimpleClass();
            $anotherSimpleClass = new AnotherSimpleClass();
            $extendedClass = new ExtendedClass(new SimpleClass());

            $this->creator->registerClassResource($extendedClass);
            $this->creator->registerClassResource($anotherSimpleClass);


            /** @var MoreExtendedClassWithExtendedAndSimpleDependency $moreExtendedClass */
            $moreExtendedClass = $this->creator->create(MoreExtendedClassWithExtendedAndSimpleDependency::class);

            /** @var MoreExtendedClassWithExtendedAndSimpleDependency $moreExtendedClassWithInjectedSimpleClass */
            $moreExtendedClassWithInjectedSimpleClass = $this->creator->createInjected(MoreExtendedClassWithExtendedAndSimpleDependency::class)->with($simpleClass)->create();

            $this->assertNotSame($moreExtendedClass, $moreExtendedClassWithInjectedSimpleClass);
            $this->assertSame($extendedClass, $moreExtendedClass->getExtendedClass());
            $this->assertNotSame($extendedClass, $moreExtendedClassWithInjectedSimpleClass->getExtendedClass());
            $this->assertNotSame($simpleClass, $moreExtendedClass->getExtendedClass()->getSimpleClass());
            $this->assertSame($simpleClass, $moreExtendedClassWithInjectedSimpleClass->getExtendedClass()->getSimpleClass());

            // Ensure that other dependencies are not re-created
            $this->assertSame($anotherSimpleClass, $moreExtendedClass->getAnotherSimpleClass());
            $this->assertSame($anotherSimpleClass, $moreExtendedClassWithInjectedSimpleClass->getAnotherSimpleClass());
        }

    }