<?php

use Andrey\JsonHandler\Attributes\JsonItemAttribute;

class SimpleTestWithArrayObject
{
    #[JsonItemAttribute(required: true)]
    public string $string = 'string';
    #[JsonItemAttribute(type: 'integer')]
    public array $arr = [ 1, 2, 3 ];
}
