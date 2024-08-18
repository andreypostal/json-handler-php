<?php

use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use Andrey\JsonHandler\JsonHandler;
use Andrey\JsonHandler\JsonHydratorTrait;
use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversTrait(JsonHydratorTrait::class)]
#[CoversMethod(JsonHandler::class, 'Decode')]
#[CoversClass(JsonItemAttribute::class)]
#[CoversClass(JsonObjectAttribute::class)]
final class HydratorTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testSimpleHydrate(): void
    {
        $json = '{"string": "str", "int": 1, "float": 1.50, "bool": false}';

        $obj = new SimpleTestObject();
        $handler = new JsonHandler();
        $handler->hydrateObject($json, $obj);

        $this->assertEquals('str', $obj->string);
        $this->assertEquals(1, $obj->int);
        $this->assertEquals(1.5, $obj->float);
        $this->assertFalse($obj->bool);
    }

    /**
     * @throws JsonException
     */
    public function testImmutableHydrate(): void
    {
        $json = '{"string": "str", "int": 1, "float": 1.50, "bool": false}';

        $obj = new SimpleTestObject();
        $handler = new JsonHandler();
        $modified = $handler->hydrateObjectImmutable($json, $obj);

        // Assert modified values
        $this->assertEquals('str', $modified->string);
        $this->assertEquals(1, $modified->int);
        $this->assertEquals(1.5, $modified->float);
        $this->assertFalse($modified->bool);

        // Assert original (default) values were not modified
        $this->assertEquals('string', $obj->string);
        $this->assertEquals(11, $obj->int);
        $this->assertEquals(11.5, $obj->float);
        $this->assertTrue($obj->bool);
    }

    /**
     * @throws JsonException
     */
    public function testHydrateWithoutOptionalItems(): void
    {
        $json = '{"string": "str", "int": 1}';
        $obj = new SimpleTestObject();
        $handler = new JsonHandler();

        $handler->hydrateObject($json, $obj);
        // Modified items
        $this->assertEquals('str', $obj->string);
        $this->assertEquals(1, $obj->int);
        // Default value items
        $this->assertEquals(11.5, $obj->float);
        $this->assertTrue($obj->bool);
    }

    /**
     * @throws JsonException
     */
    public function testHydrateWithoutRequiredItem(): void
    {
        $json = '{"int": 1}';
        $obj = new SimpleTestObject();
        $handler = new JsonHandler();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('required item <string> not found');
        $handler->hydrateObject($json, $obj);
    }

    /**
     * @throws JsonException
     */
    public function testHydrateWithMultipleLevels(): void
    {
        $json = '{"id": "myId", "child": { "string": "newString" }}';
        $obj = new WithChildObject();

        $handler = new JsonHandler();
        $handler->hydrateObject($json, $obj);

        $this->assertEquals('myId', $obj->id);
        $this->assertIsObject($obj->child);
        $this->assertEquals('newString', $obj->child->string);
    }

    /**
     * @throws JsonException
     */
    public function testHydrateWithArray(): void
    {
        $json = '{"string": "myStr", "arr": [ 5, 6 ]}';
        $obj = new SimpleTestWithArrayObject();

        $handler = new JsonHandler();
        $handler->hydrateObject($json, $obj);

        $this->assertEquals('myStr', $obj->string);
        $this->assertCount(2, $obj->arr);
        $this->assertEquals(5, $obj->arr[0]);
        $this->assertEquals(6, $obj->arr[1]);
    }

    /**
     * @throws JsonException
     */
    public function testHydrateWithInvalidArray(): void
    {
        $json = '{"string": "myStr", "arr": [ "5", 6 ]}';
        $obj = new SimpleTestWithArrayObject();

        $handler = new JsonHandler();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('expected array with items of type <integer> but found <string>');
        $handler->hydrateObject($json, $obj);
    }

    /**
     * @throws JsonException
     */
    public function testHydrateWithArrayOfObjects(): void
    {
        $json = '{"id": "myId", "children": [ { "string": "abc" } ]}';
        $obj = new WithArrayOfChildObject();

        $handler = new JsonHandler();
        $handler->hydrateObject($json, $obj);

        $this->assertEquals('myId', $obj->id);
        $this->assertCount(1, $obj->children);
        $this->assertIsObject($obj->children[0]);
        $this->assertEquals('abc', $obj->children[0]->string);
    }

    /**
     * @throws JsonException
     */
    public function testHydrateWithObjectAttr(): void
    {
        $json = '{"string": "str", "int": 1, "float": 1.50, "bool": false}';
        $obj = new SimpleTestWithObjectAttr();

        $handler = new JsonHandler();
        $handler->hydrateObject($json, $obj);

        $this->assertEquals('str', $obj->string);
        $this->assertEquals(1, $obj->int);
        $this->assertEquals(1.5, $obj->float);
        $this->assertFalse($obj->bool);
    }
}
