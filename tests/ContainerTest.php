<?php

    namespace Creator\Tests;

    use Creator\Container;
    use Creator\Creator;
    use Creator\Tests\Mocks\ArbitraryClassOnlyResolvableByFactory;
    use Creator\Tests\Mocks\ArbitraryClassWithStringValue;
    use Creator\Tests\Mocks\ArbitraryFactory;
    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\InvalidClass;
    use Creator\Tests\Mocks\MoreExtendedClass;
    use Creator\Tests\Mocks\SimpleClass;
    use Psr\Container\ContainerExceptionInterface;
    use Psr\Container\ContainerInterface;
    use Psr\Container\NotFoundExceptionInterface;

    class ContainerTest extends AbstractCreatorTest {

        /**
         * @var ContainerInterface
         */
        private $container;

        function setUp () : void {
            $creator = new Creator();

            $creator->registerPrimitiveResource('hello_world', 'Hello Container!');
            $creator->registerPrimitiveResource('i_am_callable', function(){});
            $creator->registerPrimitiveResource('i_am_array', ['foo', 'bar']);
            $creator->registerClassResource(new SimpleClass());
            $creator->registerClassResource(new ExtendedClass(new SimpleClass()), 'extended_class');
            $creator->registerFactory(ArbitraryFactory::class, ArbitraryClassOnlyResolvableByFactory::class);

            $this->container = new Container($creator);
        }

        function testExpectsContainerInterface () {
            $this->assertInstanceOf(ContainerInterface::class, $this->container);
        }

        function testExpectsPredefinedClassResource () {
            $this->assertInstanceOf(SimpleClass::class, $this->container->get(SimpleClass::class));
        }

        function testExpectsResolvedClassResource () {
            $this->assertInstanceOf(MoreExtendedClass::class, $this->container->get(MoreExtendedClass::class));
        }

        function testExpectsPredefinedPrimitiveValue () {
            $this->assertSame('Hello Container!', $this->container->get('hello_world'));
        }

        function testExpectsResolvedClassResourceWithPrimitiveIdentifier () {
            $this->assertInstanceOf(ExtendedClass::class, $this->container->get('extended_class'));
        }

        function testExpectsResolvedCallableWithPrimitiveIdentifier () {
            $this->assertIsCallable($this->container->get('i_am_callable'));
        }

        function testExpectsArrayWithPrimitiveIdentifier () {
            $this->assertIsArray($this->container->get('i_am_array'));
        }

        function testExpectsNotFoundExceptionForUndefinedValue () {
            $this->expectException(NotFoundExceptionInterface::class);

            $this->container->get('undefined value');
        }

        function testExpectsNotFoundExceptionForUnknownClass () {
            $this->expectException(NotFoundExceptionInterface::class);

            /** @noinspection PhpUndefinedClassInspection */
            $this->container->get(\UnknownClass::class);
        }

        function testExpectsContainerExceptionForInvalidClass () {
            $this->expectException(ContainerExceptionInterface::class);

            $this->container->get(InvalidClass::class);
        }

        /**
         * @param string $identifier
         * @param bool $shouldExist
         * @dataProvider provideIdentifiers
         */
        function testExpectsContainerToReturnWhetherItHasEntriesForGivenIdentifier (string $identifier, bool $shouldExist) {
            if ($shouldExist === true) {
                $this->assertTrue($this->container->has($identifier));
            } else {
                $this->assertFalse($this->container->has($identifier));
            }
        }

        /**
         * @return array
         */
        function provideIdentifiers () : array {
            /** @noinspection PhpUndefinedClassInspection */
            return [
                'simple class is pre-registered' => [SimpleClass::class, true],
                'hello world is predefined' => ['hello_world', true],
                'extended class is predefined' => ['extended_class', true],
                'callable is predefined' => ['i_am_callable', true],
                'array is predefined' => ['i_am_array', true],
                'undefined value' => ['not_exists', false],
                'inexistant class' => [\UnknownClass::class, false],
                'arbitrary class has factory' => [ArbitraryClassOnlyResolvableByFactory::class, true],
                'another arbitrary class has no factory' => [ArbitraryClassWithStringValue::class, false],
                'MoreExtendedClass is not pre-registered' => [MoreExtendedClass::class, false]
            ];
        }

    }