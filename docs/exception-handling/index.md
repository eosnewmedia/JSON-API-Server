# Exception Handling

The JSON API server will handle all exceptions which inherit from `Enm\JsonApi\Exception\JsonApiException` automatically and
respond with a JSON API error response and the correct HTTP status code.

An exception which does not inherit from `Enm\JsonApi\Exception\JsonApiException` will result in a HTTP status code 500
but the response is also a valid error response.

## Logging

The JSON API server implements `Psr\Log\LoggerAwareInterface` and can log exceptions (log level "error") to a provided 
instance of `Psr\Log\LoggerInterface`.

*****

[prev: Requests](../requests/index.md) | [back to README](../../README.md)
