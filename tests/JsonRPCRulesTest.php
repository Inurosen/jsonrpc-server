<?php

use Inurosen\JsonRPCServer\Rules\ArrayRule;
use Inurosen\JsonRPCServer\Rules\BooleanRule;
use Inurosen\JsonRPCServer\Rules\DateTimeStringRule;
use Inurosen\JsonRPCServer\Rules\DecimalRule;
use Inurosen\JsonRPCServer\Rules\IntegerRule;
use Inurosen\JsonRPCServer\Rules\NumberRule;
use Inurosen\JsonRPCServer\Rules\ObjectRule;
use Inurosen\JsonRPCServer\Rules\RequiredRule;
use Inurosen\JsonRPCServer\Rules\StringRule;

class JsonRPCRulesTest extends \PHPUnit\Framework\TestCase
{
    public function testRequiredRule()
    {
        $rule = new RequiredRule();
        $this->assertTrue($rule->handle('id', ['id' => 1]));
        $this->assertFalse($rule->handle('id', ['']));
    }

    public function testArrayRule()
    {
        $rule = new ArrayRule();
        $this->assertTrue($rule->handle(0, [[1, 2, 3]]));
        $this->assertFalse($rule->handle(0, ['']));
    }

    public function testBooleanRule()
    {
        $rule = new BooleanRule();
        $this->assertTrue($rule->handle(0, [true]));
        $this->assertFalse($rule->handle(0, ['']));
    }

    public function testDateTimeStringRule()
    {
        $rule = new DateTimeStringRule();
        $this->assertTrue($rule->handle(0, ['1970-01-01 00:00:00']));
        $this->assertFalse($rule->handle(0, ['']));
    }

    public function testDecimalRule()
    {
        $rule = new DecimalRule();
        $this->assertTrue($rule->handle(0, [1.322131]));
        $this->assertFalse($rule->handle(0, ['']));
    }

    public function testIntegerRule()
    {
        $rule = new IntegerRule();
        $this->assertTrue($rule->handle(0, [1]));
        $this->assertFalse($rule->handle(0, ['']));
    }

    public function testNumberRule()
    {
        $rule = new NumberRule();
        $this->assertTrue($rule->handle(0, [1]));
        $this->assertTrue($rule->handle(0, ['2']));
        $this->assertTrue($rule->handle(0, ['3.14']));
        $this->assertFalse($rule->handle(0, ['']));
    }

    public function testObjectRule()
    {
        $rule = new ObjectRule();
        $this->assertTrue($rule->handle(0, [$rule]));
        $this->assertTrue($rule->handle(0, [new \stdClass()]));
        $this->assertFalse($rule->handle(0, ['']));
    }

    public function testStringRule()
    {
        $rule = new StringRule();
        $this->assertTrue($rule->handle(0, ['string']));
        $this->assertTrue($rule->handle(0, ['']));
        $this->assertFalse($rule->handle(0, [1]));
    }
}
