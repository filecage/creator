# creator
creator is a simple PHP dependency injection that works with typehints and Reflection classes

* [Installation](#installation)
* [Testing](#testing)
* [Basic Usage](#basic-usage)
* [PSR-11 Container Usage](#psr-11-container-usage)
* [Injected Instances](#injected-instances)
* [Invoke Closures / Callables](#invoke-closures--callables)
* [Factories](#factories)
* [Uninstantiable Classes](#uninstantiable-classes)
* [Registering Resources](#registering-resources)
* [Exceptions](#exceptions)

## Installation
via [composer](https://getcomposer.org/)
````bash
$ composer require tholabs/creator
````

## Testing
via [phpunit](https://phpunit.de/)
````bash
$ phpunit
````


## Basic Usage
### With class constant
````php
<?php
    $creator = new \Creator\Creator;
    $myInstance = $creator->create(MyClass::class);
````
### With recursive dependencies
assuming our `MyClass` looks like this:
````php
<?php
    class MyClass {
        function __construct(AnotherClass $anotherClass) {
            $this->anotherClass = $anotherClass;
        }
    }
````
Creator will walk up the dependency tree and resolve any class which has no known instance yet.

## PSR-11 Container usage
Creator supports the [PSR-11](https://www.php-fig.org/psr/psr-11/) `psr/container` standard.
```php
<?php

    $container = new Creator\Container(new Creator\Creator());
    $myInstance = $container->get(MyClass::class);
```

The `$container->has()` will return `true` if:
* a primitive resource with given `$identifier` has been registered using `$creator->registerPrimitiveResource()`
* a class resource with given `$identifier` has been registered using `$creator->registerClassResource()`
* a class resource factory for `$identifier` has been registered using `$creator->registerFactory()`
* `$identifier` is an interface or abstract class that can be fulfilled (because another instance implementing or inheriting has been registered earlier)

It will not return `true` if a given `$identifier` _might be_ instantiable. 

## Injected Instances
Creator is able to use an independent resource registry for a single creation process.
````php
<?php
    $anotherClass = new AnotherClass();
    
    $creator = new Creator\Creator;
    $myClass = $creator->createInjected(MyClass::class)->with($anotherClass)->create();
    
    if ($myClass->anotherClass === $anotherClass) {
        echo 'We are the same!';
    }
````
Any other dependency of MyClass is being resolved the usual way (i.e. looked up via the ResourceRegistry and, if there is no instance yet, it's being created with the same injected resources.)
Creator collects dependency signatures and thus only re-creates instances that really require an injected dependency.

## Invoke Closures / Callables
Creator is able to resolve parameters of closures and callables (invokables). It supports closures and method-context array callables with the object at index 0 (using the class name at index 0 is currently not supported and will cause Creator to throw an `Unresolvable` exception).

Injecting instances is supported as well.
````php
<?php

    // Default invocation
    $creator->invoke(function(SimpleClass $simpleClass) {
        if ($simpleClass instanceof SimpleClass) {
            echo 'Everything works as expected.';
        }
    }
    
    // Injected invocation
    $simpleClassRoleModel = new SimpleClass();
    $creator->invokeInjected(function(SimpleClass $simpleClass) use($simpleClassRoleModel) {
        if ($simpleClass === $simpleClassRoleModel) {
            echo 'Injection is great';
        }
    })->with($simpleClassRoleModel)->invoke();
````

## Factories
If you have resources that can not be created without additional logic, but also should only be created once another component depends them, you can register a factory for this factory.
A factory can be a callable, an instance of `Creator\Interfaces\Factory` or a class string of a Factory (see [lazy bound factories](#lazy-bound-factories)) and can be registered for any class resource, i.e. interfaces, abstracts or normal classes.

### Global Factories
````php
<?php

    $simpleClass = new SimpleClass();
    $factory = function() use ($simpleClass) {
        return new ExtendedClass($simpleClass);
    };
    
    $creator->registerFactory($factory, ExtendedClass::class);
    
    $extendedClass = $creator->create(ExtendedClass::class);
    if ($extendedClass->getSimpleClass() === $simpleClass) {
        echo 'Factories are awesome!';
    }
````
This comes in especially handy for stuff like database connections where you only want to create a connection if a component really depends on it.

### Injected Factories
Of course, you can also register a factory as injection:
````php
<?php
    $simpleClass = new SimpleClass();
    $factory = function() use ($simpleClass) {
        return new ExtendedClass($simpleClass);
    };
    
    $extendedClass = $creator->createInjected(ExtendedClass:class)
        ->withFactory($factory, ExtendedClass::class)
        ->create();
    
    if ($extendedClass->getSimpleClass() === $simpleClass) {
        echo 'Factories are awesome!';
    }
````
Injected factories overrule globally registered factories and even globally registered resources. However, they do not overrule injected resources. (Creation order routine is: Injected Instance -> Injected Factory -> Global Instance -> Global Factory -> Create Instance)

### Self Factories
To avoid the need of a global factory, classes can also implement the `Creator\Interfaces\SelfFactory` interface.
All classes implementing this interface will not be built using their constructor; instead, they have to return a factory closure:

```php
class MyDependency implements Creator\Interfaces\SelfFactory {

    static function createSelf () : callable {
        return function(AnotherDependency $a) : MyDependency {
            return new static($a->getFoo());
        }
    }
    
    function __construct (Foo $foo) {
        $this->foo = $foo;
    }
    
}
```

It is worth nothing here that not returning an instance of the class will throw an `InvalidFactoryResult` [exception](#exceptions).

### Lazy Bound Factories
If you have factories that should not be created until they are required, you can register a lazy factory by using it's class name:

````php
<?php

    $creator->registerFactory(SimpleFactory::class, SimpleClass::class);
    
    // Creates a SimpleClass with SimpleFactory
    $simpleClass = $creator->create(SimpleClass::class);
````

All lazy bound factories are stored to and read from the ResourceRegistry that defined them:
````php
<?php

    class SimpleFactory implements Factory {
        
        function __construct(SimpleClass $simpleClass) {
            $this->simpleClass = $simpleClass;
        }
        
        function createInstance() {
            return $this->simpleClass;
        }
        
    }

    $creator->registerFactory(SimpleFactory::class, SimpleClass::class);
    
    $simpleClass = new SimpleClass();
    $simpleFactory = new SimpleFactory($simpleClass);
    
    $creator->registerClassResource($simpleFactory);
    
    $generatedSimpleClass = $creator->create(SimpleClass::class);
    if ($simpleClass === $generatedSimpleClass) {
        echo 'Congratulations, this example is completely useless and works!';
    }
````

### Factory Result Caching
All factory results are registered to their corresponding `ResourceRegistry`, i.e. a injected factory will store it's result to the injected registry and thereby make it's created resource available during this creation process only.
The only exception is a lazy bound factory with an injected dependency; in that case, the result of the factory is cached in the injection registry.

````php
<?php

    class ArbitraryFactory implements Factory {
        
        function __construct(SimpleClass $simpleClass) {
            $this->simpleClass = $simpleClass;
        }
        
        function createInstance() {
            return $this->simpleClass;
        }
        
    }

    $creator->registerFactory(ArbitraryFactory::class, ArbitraryClassWithSimpleClassDependency::class);
    
    $injectedArbitraryClass = $creator->createInjected(ArbitraryClassWithSimpleClassDependency::class)
        ->with(new SimpleClass())
        ->create();
    
    $anyArbitraryClass = $creator->create(ArbitraryClassWithSimpleClassDependency::class);
````
In the example above, the instances of `ArbitraryClassWithSimpleClassDependency` will not be the same. Creator detects that `SimpleClass` is a dependency of the registered factory and therefore create a new instance of `ArbitraryFactory` with the injected `SimpleClass`. This new factory instance is stored to the injected registry and will not affect other creations.

## Uninstantiable Classes
### Singletons
Singletons can be resolved if they implement the `Creator\Interfaces\Singleton` interface.

### Abstracts, Interfaces
If Creator stumbles upon an interface or an abstract class, it will try to look up the resource registry if any resource implements the interface / abstract class. First one is being served.

## Registering Resources
### Classes
If you want creator to use a certain instance of a class, you can register any object to Creator. It will then use this instance for any upcoming creation - a more "persistent" injection.
````php
<?php
    $a = new stdClass;
    $a->foo = 'bar';
    
    $creator = new Creator\Creator;
    $creator->registerClassResource($a);
    
    $instance = $creator->create('stdClass'); // you should not use hardcoded strings as class names; always prefer the class constant
    echo $instance->foo; // bar
````
The optional second parameter `$classResourceKey` of the method `registerClassResource` bypasses a get_class determination of the object. This might break code completion and type hinting, so use it wisely.
### Primitive (scalar) Resources
Creator supports registering scalar values by variable name.

````php
<?php

    class A {
        function __construct($foo) {
            echo $foo;
        }
    }
    
    $creator = new Creator\Creator;
    $creator->createInjected(A::class)
        ->with('bar', 'foo') // first value is the injection, second the resource key
        ->create();
````

In previous versions of Creator, there was a method to register primitive resources in the global registry.
This has been removed as it might cause unexpected behaviour and hinder future development.

However, if you *really* need it (but don't say nobody told you it's a bad idea), you can still achieve this by registering the scalar value to a `ResourceRegistry` and pass this registry while constructing your `Creator\Creator` instance. See the tests for example code.
#### Primitive resource specifics
- If an argument has a default value and Creator can not find a matching scalar value, it will use the default value.
- Registering an object with `Creation::with()` will always result in a class resource registration, i.e. registering `$creation->with($myInstance, 'foo');` will only register `$myInstance` as class foo, but never as primitive resource.

## Exceptions
All exceptions derive from `Creator\Exceptions\CreatorException`. Use this class in your catch block to catch *all* Creator-related exceptions.

Additionally, there are more specific exceptions:
* If Creator is unable to resolve a dependency, it will throw `Creator\Exceptions\Unresolvable`.
* If you are registering a factory which is not a `callable` or an instance of / a class name of a class that implements `Creator\Interfaces\Factory`, it will throw `Creator\Exceptions\InvalidFactory`.
* If a self-factory returns a class which is not an instance of self, it will throw `Creator\Exceptions\InvalidFactoryResult`.
