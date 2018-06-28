# creator
creator is a simple PHP dependency injection that works with typehints and Reflection classes

* [Installation](#installation)
* [Testing](#testing)
* [Basic Usage](#basic-usage)
* [Injected Instances](#injected-instances)
* [Invoke Closures / Callables](#invoke-closures--callables)
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

## Uninstantiable Classes
### Singletons
Singletons can be resolved if they implement the `Creator\Interfaces\Singleton` interface.
### Abstracts, Interfaces
If Creator stumbles upon an interface or an abstract class, it will try to:

1. Look up the resource registry if any resource implements the interface / abstract class. First one is being served.
2. Look up a factory by using the entities name + "Factory", i.e. the factory of `Foo\Bar\MyInterface` is `Foo\Bar\MyInterfaceFactory`

#### Additional notes on Factories
* A factory has to implement the `Creator\Interfaces\Factory` interface
* Factories are being created via `Creator::create` and thus may require further dependencies

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
* If Creator is unable to resolve a dependency, it will throw a `Creator\Exceptions\Unresolvable`.
