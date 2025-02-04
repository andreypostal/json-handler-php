<?php

namespace Andrey\JsonHandler;

interface SerializerInterface
{
    public function serialize(object $obj): array;
}
