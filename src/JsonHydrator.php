<?php
namespace Andrey\JsonHandler;

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use Andrey\JsonHandler\KeyMapping\KeyMappingStrategy;
use Andrey\JsonHandler\KeyMapping\KeyMappingUnderscore;
use InvalidArgumentException;
use JsonException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

readonly class JsonHydrator implements HydratorInterface
{
    private KeyMappingStrategy $keyStrategy;

    public function __construct(
        ?KeyMappingStrategy $keyStrategy = null,
    ) {
        $this->keyStrategy = $keyStrategy ?: new KeyMappingUnderscore();
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function hydrate(string|array $json, object|string $objOrClass): object
    {
        $jsonArr = is_string($json) ? $this->decode($json) : $json;
        $reflectionClass = new ReflectionClass($objOrClass);
        return $this->processClass($reflectionClass, $jsonArr);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    private function processClass(ReflectionClass $class, array $jsonArr): object
    {
        $instance = $class->newInstance();
        $skipAttributeCheck = ($class->getAttributes(JsonObjectAttribute::class)[0] ?? null) !== null;
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $property->setValue($instance, $this->processProperty($property, $jsonArr, $skipAttributeCheck));
        }
        return $instance;
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
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
        $key = $item->key ?? $this->keyStrategy->from($property->getName());
        if ($item->required && !array_key_exists($key, $jsonArr)) {
            throw new InvalidArgumentException(sprintf('required item <%s> not found', $key));
        }

        if ($property->getType()?->isBuiltin()) {
            return $this->handleBuiltin($jsonArr, $key, $property, $item);
        }

        return $this->handleCustomType($jsonArr[$key], $property->getType()?->getName());
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    private function handleBuiltin(array $jsonArr, string $key, ReflectionProperty $property, JsonItemAttribute $item): mixed
    {
        if ($item->type !== null && $property->getType()?->getName() === 'array') {
            $output = [];
            $classExists = class_exists($item->type);
            foreach ($jsonArr[$key] ?? [] as $k => $v) {
                $value = $v;
                if ($classExists) {
                    $value = $this->handleCustomType($value, $item->type);
                } elseif (gettype($v) !== $item->type) {
                    throw new LogicException(sprintf('expected array with items of type <%s> but found <%s>', $item->type, gettype($v)));
                }
                $output[$k] = $value;
            }
            return $output;
        }
        return $jsonArr[$key] ?? ($property->hasDefaultValue() ? $property->getDefaultValue() : null);
    }

    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    private function handleCustomType(mixed $value, string $type): mixed
    {
        $typeReflection = new ReflectionClass($type);
        if ($typeReflection->isEnum()) {
            return call_user_func($type.'::tryFrom', $value);
        }
        return $this->hydrate(
            $value,
            new ($type)(),
        );
    }

    /**
     * @throws JsonException
     */
    private function decode(string $json): mixed
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}
