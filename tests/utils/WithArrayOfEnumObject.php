<?php

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;

#[JsonObjectAttribute]
class WithArrayOfEnumObject
{
    public string $id = 'id';
    #[JsonItemAttribute(type: EnumObject::class)]
    public array $enum = [ EnumObject::Abc, EnumObject::Aaa ];
}
