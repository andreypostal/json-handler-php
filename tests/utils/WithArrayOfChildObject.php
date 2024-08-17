<?php

use Andrey\JsonHandler\JsonItemAttribute;

class WithArrayOfChildObject
{
    #[JsonItemAttribute]
    public string $id = 'id';

    #[JsonItemAttribute(type: SimpleTestObject::class)]
    public array $children;

    public function __construct()
    {
        $this->children = [
            new SimpleTestObject(),
            new SimpleTestObject(),
        ];
    }
}
