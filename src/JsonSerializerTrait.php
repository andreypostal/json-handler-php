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
                $output[$key] = $this->handleArray($item, $property->getValue($obj));
                continue;
            }

            $class = new ReflectionClass($property->getValue($obj));
            if ($class->isEnum()) {
                $output[$key] = $property->getValue($obj)->value;
                continue;
            }
            $output[$key] = $this->serialize($property->getValue($obj));
        }
        return $output;
    }

    /**
     * @param JsonItemAttribute $item
     * @param mixed $value
     * @return mixed
     * @throws ReflectionException
     * @throws JsonException
     *
     * @noinspection GetTypeMissUseInspection
     */
    private function handleArray(JsonItemAttribute $item, mixed $value): mixed
    {
        if (gettype($value) !== 'array') {
            return $value;
        }

        if (!class_exists($item->type)) {
            return $value;
        }
        $class = new ReflectionClass($item->type);
        $isEnum = $class->isEnum();

        return array_reduce(
            array: $value,
            callback: function(array $l, mixed $c) use ($isEnum): array {
                if ($isEnum) {
                    $v = $c->value;
                } else {
                    $v = $this->serialize($c);
                }
                $l[] = $v;
                return $l;
            },
            initial: [],
        );
    }
}
