# Why Falcon Container?
The complexity of existing packages providing service containers and providers led to the creation of this project. Falcon doesn't force you to create a BIND. If a bind exists for an ID, it uses it; otherwise, it proceeds with `AUTOWIRE`.

## Getting Started
First, create the container:

~~~php
$container = \HesamMousavi\FalconContainer\FalconContainer::getInstance();
~~~
## Singleton Service
If you want to use a service as a singleton:

~~~php
$container->singleton('test', Test::class);
// Or
$container->singleton(Test::class);
~~~

To execute the service in the first case:

~~~php
$container->get('test');
~~~

In the second case:

~~~php
$container->get(Test::class);
~~~

## Non-Singleton Service
If you `don't` want to use the service as a `singleton`, you need to `bind` it:

~~~php
$container->bind('test', Test::class);
// Or
$container->bind(Test::class);
~~~

Calling them is the same as with the singleton and both are accessible with `get` method:

~~~php
$container->get('test');
$container->get(Test::class);
~~~

## Using Closures
You can also use closures:

~~~php
$container->singleton('test', function ($container) {
    return $container->get(Test::class);
});
~~~

### Example
Suppose we have a class titled Test:

~~~php
namespace HesamMousavi\FalconContainer;

class Test {}
~~~

Result of Calling This Class:
~~~php
$container = \HesamMousavi\FalconContainer\FalconContainer::getInstance();

$container->singleton('test', \HesamMousavi\FalconContainer\Test::class);
//or
//$container->bind('test', \HesamMousavi\FalconContainer\Test::class);

dump($container->get('test'));
dump($container->get('test'));
~~~

Output in `Bind` Mode:
~~~
HesamMousavi\FalconContainer\Test {#6}
HesamMousavi\FalconContainer\Test {#13}
~~~
Output in `Singleton` Mode:
~~~
HesamMousavi\FalconContainer\Test {#6}
HesamMousavi\FalconContainer\Test {#6}
~~~

## Autowire Mode
If the class is not bound and called directly via the get method, it will be `automatically` resolved using reflection. This means that the autowire mode is always active, and if a bind(or singleton) is defined for a service, it uses it; otherwise, it resolves it automatically.

___
## Using `Service Provider` in Falcon Container
You can also use service providers. To do this, you need to create a class that extends `ServiceProvider`. You can have a directory called providers and place your providers there.

## Creating a ServiceProvider
~~~php
namespace HesamMousavi\FalconContainer\Providers;

use HesamMousavi\FalconContainer\FalconServiceProvider;
use HesamMousavi\FalconContainer\Test;

class TestServiceProvider extends FalconServiceProvider {
public function register() {
$this->container->singleton('test', Test::class);
}

    public function boot() {}
}
~~~

## Registering Providers
Each provider consists of two methods: `register` and `boot`. As you know, first the register method of all providers is executed, then the boot method is called.

## providers.php File
Then in a file, for example named providers.php, you should include the path to the created provider.

~~~php
<?php
return [
    \HesamMousavi\FalconContainer\Providers\TestServiceProvider::class,
];
~~~
## Providers
After creating an instance of the container, you can run all providers by calling the runProviders method and passing the path to the providers.php file.

~~~php
$container = \HesamMousavi\FalconContainer\FalconContainer::getInstance();
$container->runProviders(__DIR__.'/bootstrap/providers.php');
~~~

These steps help you use service providers in your Falcon Container plugin, adding more capabilities to your project. 🚀


