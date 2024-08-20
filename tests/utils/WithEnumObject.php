<?php

use Andrey\JsonHandler\Attributes\JsonObjectAttribute;

#[JsonObjectAttribute]
class WithEnumObject
{
    public string $id = 'id';
    public EnumObject $enum = EnumObject::Abc;
}
