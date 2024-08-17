<?php

use Andrey\JsonHandler\JsonHandler;
use Andrey\JsonHandler\JsonItemAttribute;
use Andrey\JsonHandler\JsonSerializerTrait;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversTrait(JsonSerializerTrait::class)]
#[UsesClass(JsonHandler::class)]
final class SerializerTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testSimpleSerialize(): void
    {
        $obj = new class {
            #[JsonItemAttribute]
            public string $string = 'string';
            #[JsonItemAttribute]
            public int $int = 11;
            #[JsonItemAttribute]
            public float $float = 11.50;
            #[JsonItemAttribute]
            public bool $bool = true;
        };

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

    /**
     * @throws JsonException
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
}
