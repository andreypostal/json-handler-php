<?php

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use Andrey\JsonHandler\JsonHandler;
use Andrey\JsonHandler\JsonSerializerTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversTrait(JsonSerializerTrait::class)]
#[CoversMethod(JsonHandler::class, 'Encode')]
#[CoversClass(JsonItemAttribute::class)]
#[CoversClass(JsonObjectAttribute::class)]
final class SerializerTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testSimpleSerialize(): void
    {
        $this->assertSimpleSerializedObject(new SimpleTestObject());
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testSerializeWithKeyModified(): void
    {

        $obj = new class {
            #[JsonItemAttribute(key: 'modified_key')]
            public string $string = 'string';
            #[JsonItemAttribute]
            public int $sameKey = 11;
        };

        $handler = new JsonHandler();
        $arr = $handler->serialize($obj);

        $this->assertArrayHasKey('modified_key', $arr);
        $this->assertArrayHasKey('sameKey', $arr);

        $this->assertIsInt($arr['sameKey']);
        $this->assertEquals(11, $arr['sameKey']);

        $this->assertIsString($arr['modified_key']);
        $this->assertEquals('string', $arr['modified_key']);

        JsonHandler::Encode($arr);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testSerializeMultiLevel(): void
    {
        $obj = new WithChildObject();

        $handler = new JsonHandler();
        $arr = $handler->serialize($obj);

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('child', $arr);

        $this->assertIsArray($arr['child']);
        $this->assertArrayHasKey('string', $arr['child']);

        $this->assertIsString($arr['id']);
        $this->assertEquals('id', $arr['id']);

        JsonHandler::Encode($arr);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testSerializeWithExtraItems(): void
    {
        $obj = new class {
            public string $string = 'string';
            #[JsonItemAttribute(key: 'my_item')]
            public int $child = 1;
        };

        $handler = new JsonHandler();
        $arr = $handler->serialize($obj);

        $this->assertArrayNotHasKey('string', $arr);
        $this->assertArrayHasKey('my_item', $arr);

        JsonHandler::Encode($arr);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testSimpleEnum(): void
    {
        $obj = new WithEnumObject();

        $handler = new JsonHandler();
        $arr = $handler->serialize($obj);

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('enum', $arr);
        $this->assertIsString($arr['enum']);
        $this->assertEquals('abc', $arr['enum']);

        JsonHandler::Encode($arr);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testArrayEnum(): void
    {
        $obj = new WithArrayOfEnumObject();

        $handler = new JsonHandler();
        $arr = $handler->serialize($obj);

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('enum', $arr);
        $this->assertIsArray($arr['enum']);
        $this->assertCount(2, $arr['enum']);
        $this->assertEquals('abc', $arr['enum'][0]);
        $this->assertEquals('aaa', $arr['enum'][1]);

        JsonHandler::Encode($arr);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testArrayOfChild(): void
    {
        $obj = new WithArrayOfChildObject();

        $handler = new JsonHandler();
        $arr = $handler->serialize($obj);

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('children', $arr);
        $this->assertIsArray($arr['children']);
        $this->assertCount(2, $arr['children']);
        $this->assertIsArray($arr['children'][0]);

        $this->assertArrayHasKey('string', $arr['children'][1]);
        $this->assertArrayHasKey('int', $arr['children'][1]);
        $this->assertIsInt($arr['children'][1]['int']);
        $this->assertEquals(11, $arr['children'][1]['int']);

        $this->assertIsFloat($arr['children'][1]['float']);
        $this->assertEquals(11.50, $arr['children'][1]['float']);

        JsonHandler::Encode($arr);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testSerializeWithObjectAttr(): void
    {
        $this->assertSimpleSerializedObject(new SimpleTestWithObjectAttr());
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testSerializeWithMixedAttrs(): void
    {
        $this->assertSimpleSerializedObject(new MixedAttributesObject());
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    private function assertSimpleSerializedObject(object $obj): void
    {
        $handler = new JsonHandler();
        $arr = $handler->serialize($obj);

        $this->assertArrayHasKey('string', $arr);
        $this->assertArrayHasKey('int', $arr);
        $this->assertArrayHasKey('float', $arr);
        $this->assertArrayHasKey('bool', $arr);

        $this->assertIsBool($arr['bool']);
        $this->assertTrue($arr['bool']);

        $this->assertIsInt($arr['int']);
        $this->assertEquals(11, $arr['int']);

        $this->assertIsFloat($arr['float']);
        $this->assertEquals(11.50, $arr['float']);

        $this->assertIsString($arr['string']);
        $this->assertEquals('string', $arr['string']);

        // No exceptions
        $json = JsonHandler::Encode($arr);
        $this->assertStringContainsString('float', $json);
        $this->assertStringContainsString('int', $json);
        $this->assertStringContainsString('bool', $json);
        $this->assertStringContainsString('string', $json);
    }
}
