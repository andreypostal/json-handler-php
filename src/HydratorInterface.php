<?php

namespace Andrey\JsonHandler;

interface HydratorInterface
{
    public function hydrate(string|array $json, object|string $objOrClass): object;
}
