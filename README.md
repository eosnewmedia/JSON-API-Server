JSON API Server
===============
[![Build Status](https://travis-ci.org/eosnewmedia/JSON-API-Server.svg?branch=master)](https://travis-ci.org/eosnewmedia/JSON-API-Server)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a67be0a4-39d7-4392-94ce-6377d34fe688/mini.png)](https://insight.sensiolabs.com/projects/a67be0a4-39d7-4392-94ce-6377d34fe688)

Abstract server-side php implementation of the [json api specification](http://jsonapi.org/format/), based on the symfony http foundation component.

## Installation

```sh
composer require enm/json-api-server
```

*****

## Documentation
First you should read the docs at [`enm/json-api-common`](https://eosnewmedia.github.io/JSON-API-Common/) where all basic structures are defined.

1. [Basic Usage](docs/01-basics.md)
    1. [Example of Usage](docs/01-basics.md#example-of-usage)
    1. [Endpoints](docs/01-basics.md#endpoints)
    1. [Providers](docs/01-basics.md#providers)
        1. [Resource Provider](docs/01-basics.md#resource-provider)
        1. [Multiple Resource Providers](docs/01-basics.md#multiple-resource-providers)
    1. [Exception and Error Handling](docs/01-basics.md#exception-and-error-handling)
1. [Advanced Usage](docs/02-advanced.md)
    1. [Request Options](docs/02-advanced.md#request-options)
        1. [Fetch](docs/02-advanced.md#fetch)
            1. [Fetch Example](docs/02-advanced.md#fetch-example)
        1. [Create and Patch](docs/02-advanced.md#create-and-patch)
            1. [Create Example](docs/02-advanced.md#create-example)
            1. [Patch Example](docs/02-advanced.md#patch-example)
    1. [Events](docs/02-advanced.md#events)
