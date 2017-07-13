# Exception Handling

The json api server handles all exceptions which inherit from `Enm\JsonApi\Exception\JsonApiException` automatically and
respond with a json api error response and the correct http status code.

Other exceptions, if desired, must be converted to an http error response using the "handleException" method of
`Enm\JsonApi\Server\JsonApiServer`.

An exception which does not inherit from `Enm\JsonApi\Exception\JsonApiException` will result in a http status code 500.

## Logging

The json api server implements `Psr\Log\LoggerAwareInterface` and can log errors to a provided instance of 
`Psr\Log\LoggerInterface`.

*****

[prev: Requests](../requests/index.md) | [back to README](../../README.md)
