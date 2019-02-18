<?php

namespace HappyTypes;

/**
 * Class EnumerableType is used for types which has a predefined list of options.
 *
 * You need to extend this class and implement new `final public static` methods which return a specific type object.
 * Example:
 *
 *  class FlightType extends EnumerableType {
 *
 *     final public static function Departure()
 *     {
 *          return static::get('departure');
 *     }
 *
 *     final public static function Arrival()
 *     {
 *          return static::get('arrival');
 *     }
 *     // no other methods is needed. all methods which is marked as `final` will be enumerated automatically.
 *     // you can get all available types as array using OrderType::enum() method.
 *  }
 *
 * Another example:
 *
 *  Very often there is a need to have proper integer ID type for database operations.
 *  You can use static::get() to specify ID in first parameter and in second parameter you can give a more
 *  meaningful name for that. And that name could then be used in logs or to output it to user.
 *
 *
 *  class FlightType extends EnumerableType {
 *
 *     final public static function Departure()
 *     {
 *          return static::get(1, 'departure');
 *     }
 *
 *     final public static function Arrival()
 *     {
 *          return static::get(2, 'arrival');
 *     }
 *  }
 *
 *   $flightType = FlightType::Departure();
 *   echo $flightType->name(); // will return 'departure'
 *   echo $flightType->id(); // will return 1
 *
 * Convention notes:
 *
 *      All final public static methods must begin with an upper case letter and other is in camel case notation.
 *      That way looking at code we can more clearly see that it's a constant. e.g.
 *
 *               if ($flight->getType() === FlightType::Arrival()) { ... }
 *
 * Notes:
 *
 *      EnumerableType returns only one object for a given final public static method and that means that you can
 *      safely compare objects using strict equal ===  and get a correct result.
 *
 *      Equation:  $x = FlightType::Arrival() === FlightType::Arrival() === FlightType::Arrival(); ($x will be true)
 *
 *      So there is actually no overhead in memory usage as library creates only as many objects as there are options.
 *
 *
 */
abstract class EnumerableType
{
    private $id;
    private $name;

    private static $instances = [];
    private static $enumCache = [];

    /**
     * Static method which returns all available types
     *
     * @return $this[]
     */
    public static function enum()
    {
        $classKey = crc32(get_called_class()) & 0xFFFFFF;

        if (!isset(self::$enumCache[$classKey])) {
            $reflection = new \ReflectionClass(get_called_class());
            $finalMethods = $reflection->getMethods(\ReflectionMethod::IS_FINAL);

            $return = [];

            foreach ($finalMethods as $key => $method) {
                $return[] = $method->invoke(null);
            }
            self::$enumCache[$classKey] = $return;
        }

        return self::$enumCache[$classKey];
    }

    /**
     * Static method which returns a type object from specified id
     *
     * @param $id
     *
     * @return $this
     * @throws \RuntimeException
     */
    public static function fromId($id)
    {
        foreach (self::enum() as $value) {
            if ($value->id() === $id) {
                return $value;
            }
        }

        throw new \OutOfBoundsException(sprintf("%s::fromId(%s): given id doesn't exists on this enumerable type.", get_called_class(), self::valueToString($id)));
    }

    /**
     * Forbid PHP serialization
     */
    public function __sleep()
    {
        throw new \RuntimeException('PHP serialization of EnumerableType is not supported. [' . get_called_class() . ']');
    }

    /**
     * @codeCoverageIgnore
     */
    private static function valueToString($id)
    {
        if (is_null($id)) {
            return 'null';
        }

        if (is_bool($id)) {
            return $id ? 'true' : 'false';
        }

        return $id;
    }

    /**
     * @param $id
     * @param $name
     *
     * @return $this
     */
    protected static function get($id, $name = null)
    {
        if ($name === null) {
            $name = $id;
        }

        $classKey = crc32(get_called_class()) & 0xFFFFFF;

        if (!isset(self::$instances[$classKey]))
            self::$instances[$classKey] = [];

        $instances =& self::$instances[$classKey];

        $key = $id . ';' . $name;

        if (!isset($instances[$key])) {
            $reflection = new \ReflectionClass(get_called_class());
            $instance = $reflection->newInstanceWithoutConstructor();

            $refConstructor = $reflection->getConstructor();
            $refConstructor->setAccessible(true);
            $refConstructor->invoke($instance, $id, $name);

            $instances[$key] = $instance;
        }

        return $instances[$key];
    }

    /**
     * @param $id
     * @param $name
     */
    protected function __construct($id = null, $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return int|string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }
}
