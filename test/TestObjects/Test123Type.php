<?php

namespace HappyTypes\Test\EnumerableType\TestObjects;

use HappyTypes\EnumerableType;

class Test123Type extends EnumerableType
{
    final public static function First()
    {
        return static::get('first');
    }

    final public static function Second()
    {
        return static::get('second');
    }

    final public static function Third()
    {
        return static::get('third');
    }
}