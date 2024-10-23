<?php

namespace Yolo\Di;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Yolo\Di\Annotations\Initializer;
use Yolo\Di\Annotations\Singleton;
use Yolo\Di\Errors\CircularDependencyException;
use Yolo\Di\Errors\ParameterTypeEmptyException;

/**
 * Dependency Injection.
 */
class DI
{
    /**
     * @var array $instances The array of singleton instances.
     */
    private static array $instances = [];

    /**
     * @var ReflectionClass[] $reflections The cache for reflection objects.
     */
    private static array $reflections = [];

    /**
     * @var array $creatingInstances The stack to track currently creating instances.
     */
    private static array $creatingInstances = [];

    /**
     * @var array $initializers The cache for initializer methods.
     */
    private static array $initializers = [];

    /**
     * @var array $classMappings The cache for class mappings.
     */
    private static array $classMappings = [];

    /**
     * @var array $aliases The cache for aliases.
     */
    private static array $aliases = [];

    /**
     * Get instance of class.
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @throws ReflectionException|ParameterTypeEmptyException|CircularDependencyException
     */
    public static function use(string $class)
    {
        if (array_key_exists($class, self::$instances)) {
            return self::$instances[$class];
        }

        if (array_key_exists($class, self::$aliases)) {
            return self::use(self::$aliases[$class]);
        }

        if (array_key_exists($class, self::$classMappings)) {
            return self::use(self::$classMappings[$class]);
        }

        // Check for circular dependency
        if (in_array($class, self::$creatingInstances)) {
            throw new CircularDependencyException("Circular dependency detected for class: $class");
        }

        self::$creatingInstances[] = $class;

        if (!isset(self::$reflections[$class])) {
            self::$reflections[$class] = new ReflectionClass($class);
        }

        $reflection = self::$reflections[$class];

        $constructor = $reflection->getConstructor();

        if ($constructor) {

            $parameters = $constructor->getParameters();

            $constructorParameters = [];

            foreach ($parameters as $parameter) {

                $name = $parameter->getName();
                $type = $parameter->getType();

                if (!$type) {

                    throw new ParameterTypeEmptyException("Parameter type not found: $name");
                }

                $constructorParameters[] = self::use($type);
            }

            $instance = $reflection->newInstanceArgs($constructorParameters);
        } else {

            $instance = $reflection->newInstance();
        }

        if (!isset(self::$initializers[$class])) {
            $methods = $reflection->getMethods();
            foreach ($methods as $method) {

                foreach ($method->getAttributes() as $attribute) {

                    if ($attribute->getName() === Initializer::class) {

                        self::$initializers[$class] = $method->getName();

                        // Only one initializer method is allowed.
                        break 2;
                    }
                }
            }
        }

        if (isset(self::$initializers[$class])) {
            $instance->{self::$initializers[$class]}();
        }

        // Save instance if class is singleton.
        if (self::isSingleton($reflection->getAttributes())) {

            self::$instances[$class] = $instance;
        }

        // Remove the class from the creating instances stack
        array_pop(self::$creatingInstances);

        return $instance;
    }

    /**
     * Bind an abstract class to a concrete implementation.
     * @param string $abstract
     * @param string $concrete
     * @return void
     */
    public static function bind(string $abstract, string $concrete): void
    {
        self::$classMappings[$abstract] = $concrete;
    }

    /**
     * Unbind a class.
     * @param string $abstract
     * @return void
     */
    public static function unbind(string $abstract): void
    {
        unset(self::$classMappings[$abstract]);
    }

    /**
     * Set an instance manually. (It will override any existing instance for that class.)
     *
     * It just like to set a singleton class.
     * @param string $class
     * @param object $instance
     * @return void
     */
    public static function instance(string $class, object $instance): void
    {
        self::$instances[$class] = $instance;
    }

    /**
     * Remove an instance.
     * @param string $class
     * @return void
     */
    public static function forget(string $class): void
    {
        unset(self::$instances[$class]);
    }

    /**
     * Set an alias for a class.
     * @param string $class
     * @param string $alias
     * @return void
     */
    public static function alias(string $class, string $alias): void
    {
        self::$aliases[$alias] = $class;
    }

    /**
     * Check if class is singleton.
     * @param ReflectionAttribute[] $attributes The array of class attributes.
     * @return bool
     */
    private static function isSingleton(array $attributes): bool
    {
        foreach ($attributes as $attribute) {

            if ($attribute->getName() === Singleton::class) {

                return true;
            }
        }

        return false;
    }
}
