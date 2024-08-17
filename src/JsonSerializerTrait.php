<?php
namespace Andrey\JsonHandler;

use ReflectionClass;

trait JsonSerializerTrait
{
    public function serialize(object $obj): array
    {
        $class = new ReflectionClass($obj);
        $output = [];
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $attributes = $property->getAttributes(JsonItemAttribute::class);
            $attr = $attributes[0] ?? null;
            if ($attr === null) {
                continue;
            }
            /** @var JsonItemAttribute $item */
            $item = $attr->newInstance();
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
