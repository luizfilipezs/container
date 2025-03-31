# Changelog

## 1.0.3

- Allow chaining `set` and `setValue` methods in `Container` class.

Example:

```php
$container->set(UserServiceInterface::class, UserService::class)
    ->set(UserRepositoryInterface::class, UserRepository::class)
    ->setValue('foo', 'bar')
    ->setValue('baz', 'qux');
```

## 1.0.2

- Add `declare(strict_types=1)` to all files.

## 1.0.1

- Remove `Singleton` attribute from `ContainerEventHandler` class.
- Add reading accessibility to `Container::eventHandler` attribute.
- Add methods `getDefinitions`, `getDefinition`, `getValueDefinitions` and `getValueDefinition` to `Container`, allowing access to defined classes and values.
- Replace Prettier fomatting entirely by PHP CS Fixer.

## 1.0.0

- Container and dependencies created.
