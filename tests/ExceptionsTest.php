<?php

    namespace Creator\Tests;

    use Creator\Exceptions\InvalidFactory;
    use Creator\Exceptions\Unresolvable;
    use Creator\Exceptions\UnresolvableDependency;
    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\InvalidClass;
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
            $this->expectExceptionMessage("`Creator\Tests\Mocks\InvalidRequirementClass::__construct()` demands class `InexistantClass` for parameter `\$inexistant` but the resource is unresolvable");

            $this->creator->create(InvalidRequirementClass::class);
        }

        function testShouldThrowExceptionIfClassRequiresUnknownPrimitiveResource () {
            $this->expectException(UnresolvableDependency::class);
            $this->expectExceptionMessage('`Creator\Tests\Mocks\InvalidPrimitiveDependencyClass::__construct()` demands a primitive resource for parameter `$unknownParameter` but the resource is unresolvable');

            $this->creator->create(InvalidPrimitiveDependencyClass::class);
        }

        function testShouldThrowExceptionIfClassHasDependencyThatRequiresInexistentDependency () {
            $this->expectException(UnresolvableDependency::class);
            $this->expectExceptionMessage("`Creator\Tests\Mocks\InvalidRequirementClass::__construct()` demands class `InexistantClass` for parameter `\$inexistant` but the resource is unresolvable (inner dependency of `Creator\Tests\Mocks\InvalidNestedRequirementClass::__construct()`)");

            $this->creator->create(InvalidNestedRequirementClass::class);
        }

        function testShouldThrowExceptionIfClosureHasDependencyThatRequiresInexistentDependency () {
            $this->expectException(UnresolvableDependency::class);
            $this->expectExceptionMessage("`Closure::__invoke()` demands class `InexistantClass` for parameter `\$inexistantClass` but the resource is unresolvable");

            $this->creator->invoke(function(\InexistantClass $inexistantClass){});
        }

        function testShouldThrowExceptionIfClosureHasNestedDependencyThatRequiresInexistentDependency () {
            $this->expectException(UnresolvableDependency::class);
            $this->expectExceptionMessage("`Creator\Tests\Mocks\InvalidRequirementClass::__construct()` demands class `InexistantClass` for parameter `\$inexistant` but the resource is unresolvable (inner dependency of `Closure::__invoke()`)");

            $this->creator->invoke(function(InvalidNestedRequirementClass $inexistantClass){});
        }


        function testShouldThrowInvalidFactoryExceptionWhenRegisteringNullToGlobalRegistry () {
            $this->expectException(InvalidFactory::class);
            $this->expectDeprecationMessageMatches('/^Trying to register unsupported factory type `.+` for class `.+`$/');

            $this->creator->registerFactory(null, SimpleClass::class);
        }

        function testShouldThrowInvalidFactoryExceptionWhenRegisteringNullToInjectedRegistry () {
            $this->expectException(InvalidFactory::class);
            $this->expectDeprecationMessageMatches('/^Trying to register unsupported factory type `.+` for class `.+`$/');

            $this->creator->createInjected(SimpleClass::class)->withFactory(null, SimpleClass::class);
        }

        function testShouldThrowUnresolvableExceptionWhenUsingUnionTypes () {
            if (version_compare(PHP_VERSION, '8.0.0', '<')) {
                $this->markTestSkipped('Union Types are only relevant for PHP versions >= 8.0.0');
            }

            $this->expectException(Unresolvable::class);
            $this->expectDeprecationMessage('Union Types are unsupported in this version of Creator');

            include 'ExceptionsTestPhp8Addition.php';
        }


    }