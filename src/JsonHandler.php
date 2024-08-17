<?php

namespace Andrey\JsonHandler;

use JsonException;

final class JsonHandler
{
    use JsonSerializerTrait;
    use JsonHydratorTrait;

    /**
     * @throws JsonException
     */
    public static function Decode(string $json): array
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public static function Encode(array $jsonArr): string
    {
        return json_encode($jsonArr, JSON_THROW_ON_ERROR);
    }
}
