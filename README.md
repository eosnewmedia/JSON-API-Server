JSON API Server
===============
[![Build Status](https://travis-ci.org/eosnewmedia/JSON-API-Server.svg?branch=master)](https://travis-ci.org/eosnewmedia/JSON-API-Server)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a67be0a4-39d7-4392-94ce-6377d34fe688/mini.png)](https://insight.sensiolabs.com/projects/a67be0a4-39d7-4392-94ce-6377d34fe688)

Abstract server-side php implementation of the [json api specification](http://jsonapi.org/format/), based on the [PSR-7 HTTP message interface](http://www.php-fig.org/psr/psr-7/).

## Installation

```sh
composer require enm/json-api-server
```

## Documentation
First you should read the docs at [`enm/json-api-common`](https://eosnewmedia.github.io/JSON-API-Common/) where all basic structures are defined.

1. [Json Api Server](docs/json-api-server/index.md)
    1. [Concept](docs/json-api-server/index.md#concept)
    1. [Endpoints](docs/json-api-server/index.md#endpoints)
    1. [Usage](docs/json-api-server/index.md#usage)
    1. [Advanced Configuration](docs/json-api-server/index.md#advanced-configuration)
    1. [Logging](docs/json-api-server/index.md#logging)
1. [Request Handler](docs/request-handler/index.md)
    1. [Concept](docs/request-handler/index.md#concept)
    1. [Interface](docs/request-handler/index.md#interface)
    1. [JSON API Aware](docs/request-handler/index.md#json-api-aware)
    1. [Usage](docs/request-handler/index.md#usage)
    1. [Handler Registry](docs/request-handler/index.md#handler-registry)
    1. [Resource Providers](docs/request-handler/index.md#resource-providers)
        1. [Concept](docs/request-handler/resource-providers/index.md#concept)
        1. [Interface](docs/request-handler/resource-providers/index.md#interface)
        1. [JSON API Aware](docs/request-handler/resource-providers/index.md#json-api-aware)
        1. [Usage](docs/request-handler/resource-providers/index.md#usage)
    1. [Handler Chain](docs/request-handler/index.md#handler-chain)
    1. [Pagination](docs/request-handler/index.md#pagination)
1. [Requests](docs/requests/index.md)
    1. [Fetch](docs/requests/index.md#fetch)
    1. [Save](docs/requests/index.md#save)
    1. [Delete](docs/requests/index.md#delete)
1. [Exception handling](docs/exception-handling/index.md)

See [Change Log](CHANGELOG.md) for changes!
