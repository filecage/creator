<?php

    namespace Creator\Tests;

    use Creator\Exceptions\InvalidFactory;
    use Creator\Exceptions\Unresolvable;
    use Creator\Tests\Mocks\InvalidClass;
    use Creator\Tests\Mocks\InvalidFactoryInstanceTestUninstantiableClass;
    use Creator\Tests\Mocks\InvalidFactoryTestUninstantiableClass;
    use Creator\Tests\Mocks\InvalidPrimitiveDependencyClass;
    use Creator\Tests\Mocks\InvalidRequirementClass;
    use Creator\Tests\Mocks\SimpleClass;

    class ExceptionsTest extends AbstractCreatorTest {

        function testShouldThrowExceptionIfClassIsUninstantiableAndNoSingletonAndHasNoFactory () {
            $this->expectException(Unresolvable::class);
            $this->expectExceptionMessage('Class is neither instantiable nor implements Singleton interface');

            $this->creator->create(InvalidClass::class);
        }

        function testShouldThrowExceptionIfClassRequiresInexistentDependency () {
            $this->expectException(Unresolvable::class);
            $this->expectExceptionMessage('Dependencies can not be resolved');

            $this->creator->create(InvalidRequirementClass::class);
        }

        function testShouldThrowExceptionIfFactoryDoesNotImplementInterface () {
            $this->expectException(Unresolvable::class);
            $this->expectExceptionMessageRegExp('/^Factory \S+ does not implement required interface/');

            $this->creator->create(InvalidFactoryTestUninstantiableClass::class);
        }

        function testShouldThrowExceptionIfFactoryDoesNotReturnRequestedInstance () {
            $this->expectException(Unresolvable::class);
            $this->expectExceptionMessageRegExp('/^Create method of factory \S+ did not return instance/');

            $this->creator->create(InvalidFactoryInstanceTestUninstantiableClass::class);
        }

        function testShouldThrowExceptionIfClassRequiresUnknownPrimitiveResource () {
            $this->expectException(Unresolvable::class);
            $this->expectExceptionMessage('unknown primitive resource');

            $this->creator->create(InvalidPrimitiveDependencyClass::class);
        }

        function testShouldThrowInvalidFactoryExceptionWhenRegisteringNullToGlobalRegistry () {
            $this->expectException(InvalidFactory::class);
            $this->expectExceptionMessageRegExp('/^Trying to register unsupported factory type ".+" for class ".+"$/');

            $this->creator->registerFactory(null, SimpleClass::class);
        }

        function testShouldThrowInvalidFactoryExceptionWhenRegisteringNullToInjectedRegistry () {
            $this->expectException(InvalidFactory::class);
            $this->expectExceptionMessageRegExp('/^Trying to register unsupported factory type ".+" for class ".+"$/');

            $this->creator->createInjected(SimpleClass::class)->withFactory(null, SimpleClass::class);
        }


    }