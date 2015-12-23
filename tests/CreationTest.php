<?php

    namespace Creator\Tests;

    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\SimpleClass;
    use Creator\Tests\Mocks\SimpleClassWithPrimitiveDependencies;

    class CreationTest extends AbstractCreatorTest {

        function testExpectsMockupInstance () {
            $this->assertInstanceOf(SimpleClass::class, $this->creator->create(SimpleClass::class));
        }

        function testExpectsSameInstance () {
            $a = $this->creator->create(SimpleClass::class);
            $b = $this->creator->create(SimpleClass::class);

            $this->assertSame($a, $b);
        }

        function testExpectsDifferentInstance () {
            $a = $this->creator->create(SimpleClass::class, true);
            $b = $this->creator->create(SimpleClass::class, true);

            $this->assertNotSame($a, $b);
        }

        function testExpectsRecursiveDependencyResolution () {
            /** @var ExtendedClass $instance */
            $instance = $this->creator->create(ExtendedClass::class);
            $simpleClass = $instance->getSimpleClass();

            $this->assertInstanceOf(SimpleClass::class, $simpleClass);
        }

        function testExpectsPreviouslySetInstance () {
            $instance = new SimpleClass();
            $this->creator->registerClassResource($instance);
            $created = $this->creator->create(SimpleClass::class);

            $this->assertSame($instance, $created);
        }

        function testExpectsInstanceWithPrimitiveDependencies () {
            $fromRegistry = SimpleClassWithPrimitiveDependencies::FROM_REGISTRY;
            $fromDefault = SimpleClassWithPrimitiveDependencies::FROM_DEFAULT;

            $this->creator
                ->registerPrimitiveResource('fromRegistry', $fromRegistry)
                ->registerPrimitiveResource('fromRegistryWithDefault', $fromRegistry);

            /** @var SimpleClassWithPrimitiveDependencies $instance */
            $instance = $this->creator->create(SimpleClassWithPrimitiveDependencies::class);

            $this->assertSame($fromRegistry, $instance->getFromRegistry());
            $this->assertSame($fromRegistry, $instance->getFromRegistryWithDefault());
            $this->assertSame($fromDefault, $instance->getFromDefault());
        }

    }