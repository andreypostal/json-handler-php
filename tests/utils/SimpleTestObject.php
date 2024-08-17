<?php

use Andrey\JsonHandler\JsonItemAttribute;

class SimpleTestObject
{
    #[JsonItemAttribute(required: true)]
    public string $string = 'string';
    #[JsonItemAttribute]
    public ?int $int = 11;
    #[JsonItemAttribute]
    public ?float $float = 11.50;
    #[JsonItemAttribute]
    public ?bool $bool = true;
}
