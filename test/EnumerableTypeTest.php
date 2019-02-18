<?php

namespace HappyTypes\Test\EnumerableType;

use HappyTypes\Test\EnumerableType\TestObjects\Test123Type;
use HappyTypes\Test\EnumerableType\TestObjects\TestWithNameType;
use PHPUnit_Framework_TestCase;

class EnumerableTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_must_properly_enumerate_all_final_methods()
    {
        $list = Test123Type::enum();

        $this->assertContains(Test123Type::First(), $list);
        $this->assertContains(Test123Type::Second(), $list);
        $this->assertContains(Test123Type::Third(), $list);
        $this->assertCount(3, $list);
    }

    /**
     * @test
     */
    public function it_must_return_same_reference_on_each_option()
    {
        $this->assertSame(Test123Type::First(), Test123Type::First());
        $this->assertSame(Test123Type::Second(), Test123Type::Second());
        $this->assertSame(Test123Type::Third(), Test123Type::Third());
    }

    /**
     * @test
     */
    public function it_must_return_object_with_names_if_specified()
    {
        $this->assertEquals('yes', TestWithNameType::Yes()->name());
        $this->assertEquals('no', TestWithNameType::No()->name());
        $this->assertEquals('unknown', TestWithNameType::Unknown()->name());
    }

    /**
     * @test
     */
    public function it_must_be_resolved_properly_by_id()
    {
        $this->assertSame(Test123Type::First(), Test123Type::fromId('first'));
        $this->assertSame(Test123Type::Second(), Test123Type::fromId('second'));
        $this->assertSame(Test123Type::Third(), Test123Type::fromId('third'));

        $this->assertSame(TestWithNameType::Yes(), TestWithNameType::fromId(1));
        $this->assertSame(TestWithNameType::No(), TestWithNameType::fromId(0));
        $this->assertSame(TestWithNameType::Unknown(), TestWithNameType::fromId(null));
    }

    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function it_must_throw_exception_if_cannot_be_resolved_from_id()
    {
        Test123Type::fromId('some_non_existent_id');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage PHP serialization of EnumerableType is not supported. [HappyTypes\Test\EnumerableType\TestObjects\Test123Type]
     */
    public function it_must_forbid_php_serialization()
    {
        serialize(Test123Type::First());
    }
}
