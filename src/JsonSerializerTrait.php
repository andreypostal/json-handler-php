<?php
namespace Andrey\JsonHandler;

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use ReflectionClass;

trait JsonSerializerTrait
{
    public function serialize(object $obj): array
    {
        $class = new ReflectionClass($obj);
        $skipAttributeCheck = ($class->getAttributes(JsonObjectAttribute::class)[0] ?? null) !== null;
        $output = [];
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $attributes = $property->getAttributes(JsonItemAttribute::class);
            $attr = $attributes[0] ?? null;
            if ($attr === null && !$skipAttributeCheck) {
                continue;
            }
            /** @var JsonItemAttribute $item */
            $item = $attr?->newInstance() ?? new JsonItemAttribute();
            $key = $item->key ?? $property->name;

            if ($property->getType()?->isBuiltin()) {
                $output[$key] = $property->getValue($obj);
                continue;
            }
            $output[$key] = $this->serialize($property->getValue($obj));
        }
        return $output;
    }
}
