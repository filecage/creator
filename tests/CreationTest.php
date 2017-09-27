<?php

    namespace Creator\Tests;

    use Creator\ResourceRegistry;
    use Creator\Tests\Mocks\ExtendedClass;
    use Creator\Tests\Mocks\MoreExtendedClass;
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

            $creator = $this->getWithRegistry((new ResourceRegistry())
                ->registerPrimitiveResource('fromRegistry', $fromRegistry)
                ->registerPrimitiveResource('fromRegistryWithDefault', $fromRegistry)
            );

            /** @var SimpleClassWithPrimitiveDependencies $instance */
            $instance = $creator->create(SimpleClassWithPrimitiveDependencies::class);

            $this->assertSame($fromRegistry, $instance->getFromRegistry());
            $this->assertSame($fromRegistry, $instance->getFromRegistryWithDefault());
            $this->assertSame($fromDefault, $instance->getFromDefault());
        }

        function testExpectsDifferentCreatedInstanceButSameDependency () {
            /** @var ExtendedClass $a */
            $a = $this->creator->create(ExtendedClass::class, true);
            /** @var ExtendedClass $b */
            $b = $this->creator->create(ExtendedClass::class, true);

            $this->assertNotSame($a, $b);
        }

        function testExpectsDependencyCachingWhenForcingInstance () {
            $simpleInstance = new SimpleClass();
            $this->creator->registerClassResource($simpleInstance);

            $a = $this->creator->create(MoreExtendedClass::class, true);
            $b = $this->creator->create(MoreExtendedClass::class, true);

            $this->assertSame($simpleInstance, $a->getSimpleClass());
            $this->assertSame($simpleInstance, $b->getSimpleClass());
            $this->assertSame($a->getAnotherSimpleClass(), $b->getAnotherSimpleClass());
        }

        function testExpectsCreationWithRegisteredNullValue () {
            $creator = $this->getWithRegistry((new ResourceRegistry())->registerPrimitiveResource(SimpleClassWithPrimitiveDependencies::FROM_REGISTRY, null));

            /** @var SimpleClassWithPrimitiveDependencies $simpleInstance */
            $simpleInstance = $creator->create(SimpleClassWithPrimitiveDependencies::class);

            $this->assertNull($simpleInstance->getFromRegistry());
        }

    }