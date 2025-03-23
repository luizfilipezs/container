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
