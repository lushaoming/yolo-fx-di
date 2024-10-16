<?php

namespace Yolo\Di;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Yolo\Di\Annotations\Initializer;
use Yolo\Di\Annotations\Singleton;
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
     * Get instance of class.
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @throws ReflectionException|ParameterTypeEmptyException
     */
    public static function use(string $class)
    {
        if (array_key_exists($class, self::$instances)) {

            return self::$instances[$class];
        }

        $reflection = new ReflectionClass($class);

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

        $methods = $reflection->getMethods();
        foreach ($methods as $method) {

            foreach ($method->getAttributes() as $attribute) {

                if ($attribute->getName() === Initializer::class) {

                    $instance->{$method->getName()}();

                    // Only one initializer method is allowed.
                    break 2;
                }
            }
        }

        // Save instance if class is singleton.
        if (self::isSingleton($reflection->getAttributes())) {

            self::$instances[$class] = $instance;
        }

        return $instance;
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
