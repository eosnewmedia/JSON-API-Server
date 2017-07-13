# Exception Handling

The json api server will handle all exceptions which inherit from `Enm\JsonApi\Exception\JsonApiException` automatically and
respond with a json api error response and the correct http status code.

An exception which does not inherit from `Enm\JsonApi\Exception\JsonApiException` will result in a http status code 500
but the response is also a valid error response.

## Logging

The json api server implements `Psr\Log\LoggerAwareInterface` and can log exceptions (log level "error") to a provided 
instance of `Psr\Log\LoggerInterface`.

*****

[prev: Requests](../requests/index.md) | [back to README](../../README.md)
