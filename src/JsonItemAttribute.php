<?php
namespace Andrey\JsonHandler;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonItemAttribute
{
    public function __construct(public ?string $key = null, public bool $required = false, public ?string $type = null) {}
}
