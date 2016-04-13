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
    // don't forget to autoload
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
Creator is able to use a second and independent resource registry for each creation process. This allows bigger dependency requirements without affecting the handiness of classes.
````php
<?php
    $anotherClass = new AnotherClass();
    
    $creator = new Creator\Creator;
    $myClass = $creator->createInjected(MyClass::class)->with($anotherClass)->create();
    
    if ($myClass->anotherClass === $anotherClass) {
        echo 'We are the same!';
    } else {
        echo 'We are not the same :-(';
    }
````
Any other depdendency of MyClass is being resolved the usual way, i.e. looked up via the ResourceRegistry and, if there is no instance yet, it's being created with the same injected resources.
Creator is able to collect dependency signatures and thereby only re-creates instances that really require an injected dependency.

## Invoke Closures / Callables
Creator is able to resolve parameters of closures and callables (invokables) the usual way. It supports closures and method-context array callables with the object at index 0 (using the class name at index 0 is currently not supported and will cause Creator to throw an `Unresolvable` exception).

Of course, injecting instances is supported as well.
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
        } else {
            echo 'Injection is great, but does not work';
        }
    })->with($simpleClassRoleModel)->invoke();
````

## Uninstantiable Classes
### Singletons
Singletons can be resolved if they implement the `Creator\Interfaces\Singleton` interface.
### Abstracts, Interfaces
If Creator stumbles upon an interface or an abstract class, it will try to find a factory by using the entities name with Factory, i.e. for `Foo\Bar\MyInterface` Creator will try to find a `Foo\Bar\MyInterfaceFactory`, which has to implement the `Creator\Interfaces\Factory`. Factories are being created via `Creator::create` and can therefore contain further dependencies.


## Registering Resources
### Classes
If you want creator to use a certain instance of a class, you can register any object to Creator. It will then use this instance for any upcoming creation.
````php
<?php
    $a = new stdClass;
    $a->foo = 'bar';
    
    $creator = new Creator\Creator;
    $creator->registerClassResource($a);
    
    $instance = $creator->create('stdClass'); // you should not use hardcoded strings as class names; always prefer the class constant
    echo $instance->foo; // bar
````
Theres also a second parameter `$classResourceKey` which bypasses a get_class determination of the object. This might break code completion and type hinting, so use it wisely.
### Primitive (scalar) Resources
While it is not recommended to use them, Creator supports registering scalar values by variable name. Beware that this might cause some unexpected behaviour when working with vendor packages.
````php
<?php

    class A {
        function __construct($foo) {
            echo $foo;
        }
    }
    
    $creator = new Creator\Creator;
    $creator->registerPrimitiveResource('foo', 'bar');
    
    $creator->create(A::class); // bar
````
If a constructor argument has a default value and Creator can not find a matching scalar value, it will use the default value.


## Exceptions
If Creator is unable to resolve a dependency, it will throw a `Creator\Exceptions\Unresolvable`.
