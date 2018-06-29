<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\InvalidClass;
    use Creator\Tests\Mocks\InvalidFactoryInstanceTestUninstantiableClass;
    use Creator\Tests\Mocks\InvalidFactoryTestUninstantiableClass;
    use Creator\Tests\Mocks\InvalidPrimitiveDependencyClass;
    use Creator\Tests\Mocks\InvalidRequirementClass;
    use Creator\Tests\Mocks\SimpleClass;

    class ExceptionsTest extends AbstractCreatorTest {

        /**
         * @expectedException \Creator\Exceptions\Unresolvable
         * @expectedExceptionMessage Class is neither instantiable nor implements Singleton interface
         */
        function testShouldThrowExceptionIfClassIsUninstantiableAndNoSingletonAndHasNoFactory () {
            $this->creator->create(InvalidClass::class);
        }

        /**
         * @expectedException \Creator\Exceptions\Unresolvable
         * @expectedExceptionMessage Dependencies can not be resolved
         */
        function testShouldThrowExceptionIfClassRequiresInexistentDependency () {
            $this->creator->create(InvalidRequirementClass::class);
        }

        /**
         * @expectedException \Creator\Exceptions\Unresolvable
         * @expectedExceptionMessageRegExp /^Factory \S+ does not implement required interface/
         */
        function testShouldThrowExceptionIfFactoryDoesNotImplementInterface () {
            $this->creator->create(InvalidFactoryTestUninstantiableClass::class);
        }

        /**
         * @expectedException \Creator\Exceptions\Unresolvable
         * @expectedExceptionMessageRegExp /^Create method of factory \S+ did not return instance/
         */
        function testShouldThrowExceptionIfFactoryDoesNotReturnRequestedInstance () {
            $this->creator->create(InvalidFactoryInstanceTestUninstantiableClass::class);
        }

        /**
         * @expectedException \Creator\Exceptions\Unresolvable
         * @expectedExceptionMessage unknown primitive resource
         */
        function testShouldThrowExceptionIfClassRequiresUnknownPrimitiveResource () {
            $this->creator->create(InvalidPrimitiveDependencyClass::class);
        }

        /**
         * @expectedException \Creator\Exceptions\InvalidFactoryException
         * @expectedExceptionMessageRegExp /^Trying to register unsupported factory type ".+" for class ".+"$/
         */
        function testShouldThrowInvalidFactoryExceptionWhenRegisteringNullToGlobalRegistry () {
            $this->creator->registerFactory(null, SimpleClass::class);
        }

        /**
         * @expectedException \Creator\Exceptions\InvalidFactoryException
         * @expectedExceptionMessageRegExp /^Trying to register unsupported factory type ".+" for class ".+"$/
         */
        function testShouldThrowInvalidFactoryExceptionWhenRegisteringNullToInjectedRegistry () {
            $this->creator->createInjected(SimpleClass::class)->withFactory(null, SimpleClass::class);
        }


    }