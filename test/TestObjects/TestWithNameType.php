<?php

namespace HappyTypes\Test\EnumerableType\TestObjects;

use HappyTypes\EnumerableType;

class TestWithNameType extends EnumerableType
{
    final public static function Yes()
    {
        return static::get(1, 'yes');
    }

    final public static function No()
    {
        return static::get(0, 'no');
    }

    final public static function Unknown()
    {
        return static::get(null, 'unknown');
    }
}