# Json Handler

[![Coverage Status](https://coveralls.io/repos/github/andreypostal/json-handler-php/badge.svg)](https://coveralls.io/github/andreypostal/json-handler-php) [![Maintainability](https://api.codeclimate.com/v1/badges/63e35ff0220f02d024b9/maintainability)](https://codeclimate.com/github/andreypostal/json-handler-php/maintainability)

Just a light and simple JSON helper that will make it easy for you to deal with json and objects.

## Installation

```
composer require andreypostal/json-handler-php
```

## Usage

### Classes

When creating your **Value Objects** that represent a **JSON entity** you just need
to add the ``JsonItemAttribute`` to each property that will be present in the JSON.
```php
use \Andrey\JsonHandler\Attributes\JsonItemAttribute;

// { "id": 123, "name": "my name" }
class MyObject {
    #[JsonItemAttribute]
    public int $id;
    #[JsonItemAttribute]
    public name $name;
}
```

In the case of the entire object being a JsonObject with a direct 1:1 match (or perfect mirror of the keys), you can use the ``JsonObjectAttribute``
```php
use \Andrey\JsonHandler\Attributes\JsonObjectAttribute;

// { "id": 123, "name": "my name" }
#[JsonObjectAttribute]
class MyObject {
    public int $id;
    public string $name;
}
```

If your **Value Object** has some property that **won't be present** in the JSON, you can
just omit the attribute for it and the other ones will be processed normally.
```php
use \Andrey\JsonHandler\Attributes\JsonItemAttribute;

// { "id": 123 }
class MyObject {
    #[JsonItemAttribute]
    public int $id;
    public int $myAppGeneratesIt;
}
```

In case the items are required to exist in the JSON being processed, you must add the required flag in the attribute.
```php
use \Andrey\JsonHandler\Attributes\JsonItemAttribute;

// { "id": 123 } or { "id": 123, "name": "my name" }
class MyObject {
    #[JsonItemAttribute(required: true)]
    public int $id;
    #[JsonItemAttribute]
    public string $name;
}
```

When some of the keys in your JSON are different from your object, you can include the JSON key in the attribute.
```php
use \Andrey\JsonHandler\Attributes\JsonItemAttribute;

// { "customer_name": "the customer name" }
class MyObject {
    #[JsonItemAttribute(key: 'customer_name')]
    public string $name;
}
```

Also, if you have a property that is an array of other object, you must inform the class in the attribute using the ``type`` option.
This will work as a hint so the hydrator can instantiate the appropriate object.
```php
use \Andrey\JsonHandler\JsonItemAttribute;
use \MyNamespace\MyOtherObj;

// { "list": [ { "key": "value" } ] }
class MyObject {
    /** @var MyOtherObj[] */
    #[JsonItemAttribute(type: MyOtherObj::class)]
    public array $list;
}
```

The type option can be used to validate that all the items in an array have some desired type as well, like "string", "integer"...

### Handler

In order to utilize the definitions mentioned above, you must utilize the ``JsonHandler``. Two traits are available as well,
the ``JsonHydratorTrait`` and ``JsonSerializerTrait`` that provide the methods both for serialization and hydration.

```php
use \Andrey\JsonHandler\JsonHandler;
use \MyNamespace\MyObject;

$handler = new JsonHandler();

$myObject = new MyObject();

// This parses the json string and hydrates the original object, modifying it
$handler->hydrateObject($jsonString, $myObject);

// If you don't want to modify the original object you can use the immutable hydration
$hydratedObject = $handler->hydrateObjectImmutable($jsonString, $myObject);

// You can also use an array to hydrate the object
$handler->hydrateObject($jsonArr, $myObject);

// And to fetch the information as an array you can just serialize it using the handler.
// This allows you to easily implement the JsonSerializable interface in your object.
$arr = $handler->serialize($myObject);

// The json handler also provides the methods to decode and encode
$jsonString = JsonHandler::Encode($arr);
$jsonArr = JsonHandler::Decode($jsonString);
```
