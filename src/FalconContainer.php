<?php

namespace HesamMousavi\FalconContainer;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;


class FalconContainer extends Singleton implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];

    public function singleton(string $id, string|null|\Closure $concrete = null): void
    {
        $this->bind($id, $concrete, true);
    }

    public function bind(string $id, string|null|\Closure $concrete = null, bool $shared = false): void
    {
        if (!$this->has($id)) {
            $this->bindings[$id] = ['concrete' => $concrete ?? $id, 'shared' => $shared];
        }
    }

    public function has(string $id): bool
    {
        return \array_key_exists($id, $this->bindings);
    }

    /**
     * @throws ReflectionException
     */
    protected function resolve(string|\Closure $concrete)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        $reflector = new ReflectionClass($concrete);
        if (!$reflector->isInstantiable()) {
            throw new ReflectionException("Class $concrete is not instantiable.");
        }

        $constructor = $reflector->getConstructor();
        if (\is_null($constructor)) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    protected function getDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencyClass = $parameter->getType();
            if ($dependencyClass && !$dependencyClass->isBuiltin()) {
                $dependencies[] = $this->get($dependencyClass->getName());
            } else {
                $dependencies[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
            }
        }

        return $dependencies;
    }

    public function get(string $id)
    {
        $resolved = null;

        // Handle singleton instances
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        } else {
            // Handle non-bindings
            if (!$this->has($id)) {
                if (!\class_exists($id)) {
                    return null;
                } else {
                    try {
                        return $this->resolve($id);
                    } catch (ReflectionException $exception) {
                        echo($exception->getMessage());
                    }
                }
            }
        }

        // Handle bindings
        try {
            $resolved = $this->resolve($this->bindings[$id]['concrete']);
        } catch (ReflectionException $exception) {
            echo($exception->getMessage());
        }

        if ($this->shared($id)) {
            $this->instances[$id] = $resolved;
        }

        return $resolved;
    }

    protected function shared($id)
    {
        return $this->bindings[$id]['shared'];
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function getMethod($class, $method)
    {
        $object = $this->get($class);
        $reflectionMethod = new ReflectionMethod($object, $method);
        $parameters = $reflectionMethod->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencyClass = $parameter->getType();
            if ($dependencyClass && !$dependencyClass->isBuiltin()) { // Check if it's a class and not a built-in type
                $dependencies[] = $this->get($dependencyClass->getName());
            }
        }

        return $reflectionMethod->invokeArgs($object, $dependencies);
    }

    public function runProviders(string $path): void
    {
        $providers = require_once $path;
        $provider_instances = new \WeakMap();

        foreach ($providers as $provider) {
            if (\is_subclass_of($provider, FalconServiceProvider::class)) {
                $instance = new $provider($this);
                $provider_instances[$instance] = true;
            }
        }

        foreach ($provider_instances as $instance => $bool) {
            $instance->register();
        }

        foreach ($provider_instances as $instance => $bool) {
            $instance->boot();
            unset($provider_instances[$instance]);
        }
    }

}
