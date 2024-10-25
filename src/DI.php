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
     * @var array $classes The cache for classes.
     */
    private static array $classes = [];

    /**
     * Get instance of class.
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @throws ReflectionException|ParameterTypeEmptyException|CircularDependencyException
     */
    public static function use(string $class)
    {
        // Resolve class if it's an alias.
        $resolvedClass = self::resolveClass($class);

        if (array_key_exists($resolvedClass, self::$instances)) {
            return self::$instances[$resolvedClass];
        }

        // Check for circular dependency
        if (in_array($resolvedClass, self::$creatingInstances)) {
            throw new CircularDependencyException("Circular dependency detected for class: $class");
        }

        self::$creatingInstances[] = $resolvedClass;

        $reflection = self::resolveReflection($resolvedClass);

        $constructorParameters = self::resolveConstructorParameters($reflection);
        if ($constructorParameters) {

            $instance = $reflection->newInstanceArgs($constructorParameters);
        } else {

            $instance = $reflection->newInstance();
        }

        if (!isset(self::$initializers[$resolvedClass])) {

            $initializerMethod = self::findInitializerMethod($reflection);
            if ($initializerMethod) {
                self::$initializers[$resolvedClass] = $initializerMethod;
            }
        }

        if (isset(self::$initializers[$resolvedClass])) {
            $instance->{self::$initializers[$resolvedClass]}();
        }

        // Save instance if class is singleton.
        if (self::isSingleton($reflection->getAttributes(Singleton::class))) {

            self::$instances[$resolvedClass] = $instance;
        }

        // Remove the class from the creating instances stack
        array_pop(self::$creatingInstances);

        return $instance;
    }

    /**
     * Resolve class reflection.
     * @param string $class
     * @return ReflectionClass
     * @throws ReflectionException
     */
    private static function resolveReflection(string $class): ReflectionClass
    {
        if (!isset(self::$reflections[$class])) {

            self::$reflections[$class] = new ReflectionClass($class);
            self::$classes[] = $class;

            // Limit the number of reflections to prevent memory leaks
            if (count(self::$classes) > 100) {

                // Remove the first class from the classes array and unset its reflection
                $removedClass = array_shift(self::$classes);
                unset(self::$reflections[$removedClass]);
            }
        }

        return self::$reflections[$class];
    }

    /**
     * Resolve class name.
     * @param string $class
     * @return string
     */
    private static function resolveClass(string $class): string
    {
        if (isset(self::$aliases[$class])) {
            return self::resolveClass(self::$aliases[$class]);
        }
        if (isset(self::$classMappings[$class])) {
            return self::resolveClass(self::$classMappings[$class]);
        }
        return $class;
    }

    /**
     * Resolve constructor parameters.
     * @param ReflectionClass $reflection
     * @return array
     * @throws CircularDependencyException
     * @throws ParameterTypeEmptyException
     * @throws ReflectionException
     */
    private static function resolveConstructorParameters(ReflectionClass $reflection): array
    {
        $parameters = [];
        $constructor = $reflection->getConstructor();
        if ($constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                $type = $parameter->getType();
                if (!$type) {
                    throw new ParameterTypeEmptyException("Parameter type not found: " . $parameter->getName());
                }
                $parameters[] = self::use($type->getName());
            }
        }

        return $parameters;
    }

    /**
     * Find initializer method.
     * @param ReflectionClass $reflection
     * @return string|null
     */
    private static function findInitializerMethod(ReflectionClass $reflection): ?string
    {
        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes(Initializer::class) as $ignored) {
                return $method->getName();
            }
        }
        return null;
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
