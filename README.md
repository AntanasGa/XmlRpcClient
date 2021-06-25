XmlRpcClient
============

Easy way to make XMLRPC client requests

## Documentation

### instalation
installation using composer:

```json
"require": {
    "AntanasGa/XmlRpcClient": "^0.1.0"
}
```

### Usage

Interfaces trough `Client` class.

`__construct` - takes string value of server URL and request timeout time in seconds (default is 30 secs)
`__call` - call is used for sending requests to XMLRPC server, method name sets `<methodname>`
`errorInfo` - method returns `AntanasGa\XmlRpcDecode\ResponseError` object with `faultCode` and `faultString` values

```php

use AntanasGa\XmlRpcClient\Client;

$connection = new Client('http://localhost:8069');
$response = $connection->posts('search');

if ($connection->errorInfo() !== null) {
    // handle error
}

```
