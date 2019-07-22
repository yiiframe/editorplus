<?php declare(strict_types=1);

namespace Casoa\Yii\EditorPlus\Tests;

use ReflectionClass;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Invokes object method, even if it is private or protected.
     * @param object $object object.
     * @param string $method method name.
     * @param array $args method arguments
     * @return mixed method result
     * @throws \ReflectionException
     */
    protected function invoke($object, string $method, array $args = [])
    {
        $classReflection = new ReflectionClass(get_class($object));
        $methodReflection = $classReflection->getMethod($method);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($object, $args);
        $methodReflection->setAccessible(false);
        return $result;
    }
}
