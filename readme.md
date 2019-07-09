# JSON-RPC 2.0 Server for PHP
## Requirements
* PHP 7.1+

## Installation
```
composer require inurosen/jsonrpc-server
```

## Usage
### 1. Configure
#### OOP style
```php
class FooService {
    public function bar($params) {
        return $params;
    }
}

MethodRegistry::register('hello.world', 'FooService@bar', HelloWorldValidator::class);

```

#### Closure style
```php
MethodRegistry::register('hello.world', function ($params) {
    return 'Hello world!';
}, HelloWorldValidator::class);
```

### 2. Execute
```php
$jsonRpcServer = new \Inurosen\JsonRPCServer\JsonRPCService();

$jsonRpcServer->call('{"jsonrpc": "2.0", "method": "hello.world", "params": {"param1": 1, "param2": 2}, "id": 1}');
$result = $jsonRpcServer->getResponse()->toString();

echo $result;
```
Result will be a JSON-RPC result.

### 3. Validate
Validators must implement `Inurosen\JsonRPCServer\Interfaces\ValidatorInterface`.
Extending `Inurosen\JsonRPCServer\Validator` and assigning rules is enough.
```php
public function rules(): array
{
    return [
        'id' => [
            \Inurosen\JsonRPCServer\Rules\RequiredRule::class,
            \Inurosen\JsonRPCServer\Rules\IntegerRule::class,
        ],
    ];
}
```
Or if your params is not object but a scalar or simple value
```php
public function rules(): array
{
    return [
        \Inurosen\JsonRPCServer\Rules\RequiredRule::class,
        \Inurosen\JsonRPCServer\Rules\IntegerRule::class,
    ];
}
```

