<?php

    namespace Creator\Tests;

    use Creator\Exceptions\InvalidFactory;
    use Creator\Exceptions\Unresolvable;
    use Creator\Exceptions\UnresolvableDependency;
    use Creator\Tests\Mocks\InvalidClass;
    use Creator\Tests\Mocks\InvalidFactoryInstanceTestUninstantiableClass;
    use Creator\Tests\Mocks\InvalidFactoryTestUninstantiableClass;
    use Creator\Tests\Mocks\InvalidNestedRequirementClass;
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
            $this->expectException(UnresolvableDependency::class);
            $this->expectExceptionMessage("'Creator\Tests\Mocks\InvalidRequirementClass::__construct()' demands class 'InexistantClass' as '\$inexistant', but the resource is unknown");

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
            $this->expectException(UnresolvableDependency::class);
            $this->expectExceptionMessage('\'Creator\Tests\Mocks\InvalidPrimitiveDependencyClass::__construct()\' demands a primitive resource as \'$unknownParameter\', but the resource is unknown');

            $this->creator->create(InvalidPrimitiveDependencyClass::class);
        }

        function testShouldThrowExceptionIfClassHasDependencyThatRequiresInexistentDependency () {
            $this->expectException(UnresolvableDependency::class);
            $this->expectExceptionMessage("'Creator\Tests\Mocks\InvalidRequirementClass::__construct()' demands class 'InexistantClass' as '\$inexistant', but the resource is unknown when resolving 'Creator\Tests\Mocks\InvalidNestedRequirementClass::__construct()'");

            $this->creator->create(InvalidNestedRequirementClass::class);
        }

        function testShouldThrowExceptionIfClosureHasDependencyThatRequiresInexistentDependency () {
            $this->expectException(UnresolvableDependency::class);
            $this->expectExceptionMessage("'Closure::__invoke()' demands class 'InexistantClass' as '\$inexistantClass', but the resource is unknown");

            $this->creator->invoke(function(\InexistantClass $inexistantClass){});
        }

        function testShouldThrowExceptionIfClosureHasNestedDependencyThatRequiresInexistentDependency () {
            $this->expectException(UnresolvableDependency::class);
            $this->expectExceptionMessage("'Creator\Tests\Mocks\InvalidRequirementClass::__construct()' demands class 'InexistantClass' as '\$inexistant', but the resource is unknown when resolving 'Closure::__invoke()'");

            $this->creator->invoke(function(InvalidNestedRequirementClass $inexistantClass){});
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