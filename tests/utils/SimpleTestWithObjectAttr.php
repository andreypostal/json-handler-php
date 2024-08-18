<?php
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;

#[JsonObjectAttribute]
class SimpleTestWithObjectAttr
{
    public string $string = 'string';
    public ?int $int = 11;
    public ?float $float = 11.50;
    public ?bool $bool = true;
}
