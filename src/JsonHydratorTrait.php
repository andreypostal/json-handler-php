<?php
namespace Andrey\JsonHandler;

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use InvalidArgumentException;
use JsonException;
use LogicException;
use ReflectionClass;
use ReflectionProperty;

trait JsonHydratorTrait
{
    /**
     * @throws JsonException
     */
    public function hydrateObjectImmutable(string|array $json, object $obj): object
    {
        return $this->hydrateObject($json, clone $obj);
    }

    /**
     * @throws JsonException
     */
    public function hydrateObject(string|array $json, object $obj): object
    {
        $jsonArr = is_string($json) ? JsonHandler::Decode($json) : $json;
        $reflectionClass = new ReflectionClass($obj);
        $data = $this->processClass($reflectionClass, $jsonArr);
        if ($reflectionClass->hasMethod('hydrate')) {
            $obj->hydrate($data);
        } else {
            foreach ($data as $key => $value) {
                $obj->{$key} = $value;
            }
        }
        return $obj;
    }

    /**
     * @throws JsonException
     */
    private function processClass(ReflectionClass $class, array $jsonArr): array
    {
        $skipAttributeCheck = ($class->getAttributes(JsonObjectAttribute::class)[0] ?? null) !== null;
        $output = [];
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $output[$property->getName()] = $this->processProperty($property, $jsonArr, $skipAttributeCheck);
        }
        return $output;
    }

    /**
     * @throws JsonException
     */
    private function processProperty(ReflectionProperty $property, array $jsonArr, bool $skipAttributeCheck): mixed
    {
        $attributes = $property->getAttributes(JsonItemAttribute::class);
        $attr = $attributes[0] ?? null;
        if ($attr === null && !$skipAttributeCheck) {
            return null;
        }

        /** @var JsonItemAttribute $item */
        $item = $attr?->newInstance() ?? new JsonItemAttribute();
        $key = $item->key ?? $property->getName();
        if ($item->required && !array_key_exists($key, $jsonArr)) {
            throw new InvalidArgumentException(sprintf('required item <%s> not found', $key));
        }

        if ($property->getType()?->isBuiltin()) {
            if ($item->type !== null && $property->getType()?->getName() === 'array') {
                $output = [];
                $classExists = class_exists($item->type);
                foreach ($jsonArr[$key] ?? [] as $k => $v) {
                    $value = $v;
                    if ($classExists) {
                        $value = $this->hydrateObject($v, new $item->type);
                    } elseif (gettype($v) !== $item->type) {
                        throw new LogicException(sprintf('expected array with items of type <%s> but found <%s>', $item->type, gettype($v)));
                    }
                    $output[$k] = $value;
                }
                return $output;
            }
            return $jsonArr[$key] ?? $property->getDefaultValue();
        }

        return $this->hydrateObject(
            $jsonArr[$key],
            new ($property->getType()?->getName())(),
        );
    }
}
