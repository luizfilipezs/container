# luizfilipezs/container

This library implements a dependency injection system.

## Minimum requirements

- PHP 8.4
- Composer

## Installation

Inside your project folder, run:

```bash
composer require luizfilipezs/container
```

## Usage

### Getting a definition

```php
$container = new Container();
$container->set(MyInterface::class, MyClass::class);
$myObject = $container->get(MyInterface::class);
```

The above example creates a new instance of `MyClass`, typed as `MyInterface`.

### Setting a definition

#### Class string

```php
$container->set(UserServiceInterface::class, UserService::class);
```

#### Class instance

```php
$container->set(UserServiceInterface::class, new UserService());
```

#### Closure

```php
$container->set(UserServiceInterface::class, fn() => new UserService());
```

### Checking for a definition

```php
if ($container->has(UserServiceInterface::class)) {
```

### Removing a definition

```php
$container->remove(UserServiceInterface::class);
```

### Setting a singleton

Singleton classes are automatically recognized via `#[Singleton]` attribute.

```php
use Luizfilipezs\Container\Attributes\Singleton;

#[Singleton]
class UserService
{
  public string $foo = 'bar';
}

$userService1 = $container->get(UserService::class);
$userService2 = $container->get(UserService::class);

$userService1->foo = 'baz';
echo $userService2->foo; // 'baz'
```

If you set a singleton class as definition for an interface, both the interface and the class will be set with the actual instance of the class as soon as it is created:

```php
$container->set(UserServiceInterface::class, UserService::class);

$userServiceViaInterface = $container->get(UserServiceInterface::class);
$userServiceViaInterface->setFoo('baz');

$userServiceViaClass = $container->get(UserService::class);
$userServiceViaClass->getFoo(); // 'baz'
```

### Setting a class with lazy constructor

Lazy constructor was natively implemented in PHP 8.4. It allows an object to be created without its `__construct` method getting called until
an attribute is used.

```php
use Luizfilipezs\Container\Attributes\Lazy;

#[Lazy]
class UserService
{
    public function __contruct(private readonly UserRepositoryInterface $userRepository)
    {
        echo 'Constructor was called.';
    }

    public function getAll(): array
    {
        return $this->userRepository->findAll();
    }
}

$userService = $container->get(UserService::class);
$users = $userService->getAll(); // 'Constructor was called.'
```

It is also possible to disable initialization on reading specific properties:

```php
use Luizfilipezs\Container\Attributes\{Lazy, LazyInitializationSkipped};

#[Lazy]
class MyClass
{
    #[LazyInitializationSkipped]
    public string $skippedProp = 'foo';
    public string $normalProp = 'bar';

    public function __construct()
    {
        echo 'Constructor was called.';
    }
}

$myInstance = $container->get(MyClass::class);

$myInstance->skippedProp;
$myInstance->normalProp; // 'Constructor was called.'
```

#### Capturing lazy construction

To know when a lazy `__construct` gets called you can use `ContainerEventHandlerInterface`:

```php
use Luizfilipezs\Container\Enums\ContainerEvent;
use Luizfilipezs\Container\Interfaces\ContainerEventHandlerInterface;

$eventHandler = $container->get(ContainerEventHandlerInterface::class);
$eventHandler->on(
    event: ContainerEvent::LAZY_CLASS_CONSTRUCTED,
    callback: static function (string $className, object $instance) {
        echo "{$className}::__construct was called.";
    },
);

$instance = $container->get(MyClass::class);
$instance->foo; // 'MyClass::__construct was called.'
```

### Setting non-class definitions

You can set a definition for any value, allowing it to get automatically injected even if it is not a class.

```php
use Luizfilipezs\Container\Attributes\Inject;

enum ApiConstant: string
{
  case API_KEY = 'API_KEY';
  case API_SECRET = 'API_SECRET';
}

$container->setValue(ApiConstant::API_KEY, 'my_api_key');
$container->setValue(ApiConstant::API_SECRET, 'my_api_secret');

class ApiService
{
  public function __construct(
    #[Inject(ApiConstant::API_KEY)] string $apiKey,
    #[Inject(ApiConstant::API_SECRET)] string $apiSecret,
  ) {}
}

// call __construct with: 'my_api_key', 'my_api_secret'
$apiService = $container->get(ApiService::class);
```

Any type of value can be set, but it will be strictly compared to the parameter type:

```php
$container->setValue('SOME_INT', 123);

class MyClass
{
  public function __contruct(#[Inject('SOME_INT')] float $value) {}
}

$object = $container->get(MyClass::class);
// ContainerException: Container cannot inject "SOME_INT". It is not the same
// type as the parameter. Expected float, got int.
```

#### Value definition methods

```php
$container->setValue('key', 'value');
$value = $container->getValue('key'); // 'value'
$exists = $container->hasValue('key'); // true
$container->removeValue('key');
```

### Advanced options

`Container` constructor has three parameters:

- `strict` (defaults to `false`): if `true`, only definitions set explicitly (via `set()`) will be provided.
- `skipNullableClassParams` (defaults to `true`): if `true`, nullable constructor parameters typed as a class or an interface will always be set to `null`, except if the parameter has the `Inject` attribute bound to it.
- `skipNullableValueParams` (defaults to `true`): if `true`, nullable constructor parameters typed as a primitive type will always be set to `null`, except if the parameter has the `Inject` attribute bound to it.

#### Example with `strict`:

```php
$container = new Container(strict: true);

// ContainerException, because there is no explicit definition for "SomeClass"
$instance = $container->get(SomeClass::class);
```

#### Examples with `skipNullableClassParams`

If `true`:

```php
$container = new Container(skipNullableClassParams: true);
$instance = $container->get(SomeClass::class);

$instance->nullableDependency; // null
```

If `false`:

```php
$container = new Container(skipNullableClassParams: false);
$instance = $container->get(SomeClass::class);

$instance->nullableDependency; // object
```

#### Examples with `skipNullableValueParams`

If `true`:

```php
$container = new Container(skipNullableClassParams: true);
$instance = $container->get(SomeClass::class);

$instance->nullableString; // null
```

If `false`:

```php
$container = new Container(skipNullableClassParams: false);
$instance = $container->get(SomeClass::class);

$instance->nullableString; // error, because string cannot be injected
```

In both examples above, **if the nullable parameter was bound to the `Inject` attribute, the value would be injected anyway**, because it forces injection.

## Contributing

### Forking

At the top of the Github repository page, click the **Fork** button. Then clone the forked repository to your machine.

### Installing dependencies

```bash
npm install # install Prettier package
composer update # install composer dependencies
```

### Testing

Run:

```bash
./vendor/bin/phpunit tests
```

When making changes to the codebase, remember to create tests that cover all scenarios.
