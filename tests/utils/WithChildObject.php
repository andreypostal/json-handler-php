<?php

use Andrey\JsonHandler\JsonItemAttribute;

class WithChildObject
{
    #[JsonItemAttribute]
    public string $id = 'id';

    #[JsonItemAttribute]
    public SimpleTestObject $child;

    public function __construct()
    {
        $this->child = new SimpleTestObject();
    }
}
