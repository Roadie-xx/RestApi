<?php

namespace Test\RoadieXX;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

trait PrivatePropertyTrait
{
    /**
     * @throws ReflectionException
     */
    public function getPrivateProperty($object, $propertyName)
    {
        $reflectedClass = new ReflectionClass($object);
        $reflection = $reflectedClass->getProperty($propertyName);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }

    /**
     * @throws ReflectionException
     */
    public function setPrivateProperty($object, $propertyName, $value): void
    {
        $reflectedClass = new ReflectionClass($object);
        $reflection = $reflectedClass->getProperty($propertyName);

        $reflection->setValue($object, $value);
    }

    /**
     * @throws ReflectionException
     */
    public function invokePrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflectionMethod = new ReflectionMethod($object, $methodName);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $parameters);
    }
}