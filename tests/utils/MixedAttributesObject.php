<?php

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;

#[JsonObjectAttribute]
class MixedAttributesObject
{
    public string $string = 'string';
    public ?int $int = 11;
    public ?float $float = 11.50;
    #[JsonItemAttribute(required: true)]
    public ?bool $bool = true;
}
