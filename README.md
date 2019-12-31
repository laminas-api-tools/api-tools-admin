Laminas API Tools Admin
===============

[![Build Status](https://travis-ci.org/laminas-api-tools/api-tools-admin.png)](https://travis-ci.org/laminas-api-tools/api-tools-admin)

Introduction
------------

The `api-tools-admin` module delivers the backend management API and frontend Admin UI used to
manage APIs in Laminas API Tools.

> ### NOTE
>
> **DO NOT** enable this module in production systems.

Requirements
------------

Please see the [composer.json](composer.json) file.

Installation
------------

Run the following `composer` command:

```console
$ composer require --dev "laminas-api-tools/api-tools-admin"
```

And then run `composer install` to ensure the module is installed.

Finally, add the module name to your project's `config/application.config.php` under the `modules`
key:

```php
return array(
    /* ... */
    'modules' => array(
        /* ... */
        'Laminas\ApiTools\Admin',
    ),
    /* ... */
);
```

Typically, this module should be used along with
[laminas-development-mode](https://github.com/laminas/laminas-development-mode) in order to conditionally
enable the module in your application. When doing so, you will add the module to your project's
`config/development.config.php.dist` file instead of the `config/application.config.php` file, and
enable it via `php public/index.php development enable`.

Upgrading
---------

We strive to make upgrading as simple as a `composer update`; however, from time
to time, there may be other steps involved. This section documents those.

### Initial upgrade to 1.5

If you are upgrading to version 1.5 or higher from a pre-1.5 version, there are
a few changes to be aware of.

First, version 1.5 drops the requirement for rwoverdijk/assetmanager. However,
in order to use the admin UI, you will need some way to access the public assets
provided by the UI and api-tools modules. You have three options:

1. Install rwoverdijk/assetmanager: `composer require rwoverdijk/assetmanager`.
   Be aware, however, that as of the time of the 1.5.0 release, this module is
   not compatible with v3 releases of laminas-mvc. If you are looking for a quick
   upgrade, and do not care what versions of Laminas components you
   install, this is the easiest path.

2. Install [api-tools-asset-manager](https://github.com/laminas-api-tools/api-tools-asset-manager). This
   is a Composer plugin, and operates when installing or uninstalling a package.
   If you add this, you will need to follow these steps:

    - `composer require --dev laminas-api-tools/api-tools-asset-manager`
    - `rm -Rf ./vendor`
    - `composer install`
    -
   The additional steps are necessary in order for the plugin to pick up on the
   assets from the other components.

3. Manually copy or symlink in the assets required to your public directory. As
   examples:
   
    - `ln -s vendor/laminas-api-tools/api-tools/asset/api-tools public/api-tools`
    - `ln -s vendor/laminas-api-tools/api-tools-admin-ui/dist/api-tools-ui public/api-tools-ui`

Each of the three will accomplish the goal of making the assets publicly
available via your application's web server.

### Upgrading to v3 Laminas components from 1.5

After upgrading to version 1.5 of this module, you can then upgrade your
application to take advantage of Laminas v3 components. The easiest way
to do that is to use the provided script:

```bash
$ ./vendor/bin/api-tools-upgrade-to-1.5
```

This script will update your Composer requirements and constraints, update your
modules list to list Laminas components and remove unneeded/obsolete components, and
then re-install all dependencies.

If you do not wish to use the script, or the script fails, you may manually
update your application using the following steps:

- Update your `composer.json`:
  - Remove:
    - `require.laminas/laminas`
    - `require.rwoverdijk/assetmanager`
    - `require-dev.laminas/laminastool`
  - Update:
    - `require.laminas/laminas-development-mode` constraint becomes `^3.0`
    - `require-dev.laminas/laminas-developer-tools` becomes `^1.0`
  - Add:
    - `require.laminas/laminas-cache`, with a constraint of `2.7.1`
    - `require.laminas/laminas-log`, with a constraint of `2.9`
    - `require.laminas/laminas-mvc-i18n`, with a constraint of `1.0`
    - `require-dev.laminas-api-tools/api-tools-asset-manager`, with a constraint of `^1.0`
- Update your `config/modules.config.php`:
  - Remove:
    - `AssetManager`
    - `Laminas\DevelopmentMode`
  - Add, at the top of the list:
    - `Laminas\Cache`
    - `Laminas\Db`
    - `Laminas\Filter`
    - `Laminas\Hydrator`
    - `Laminas\InputFilter`
    - `Laminas\I18n`
    - `Laminas\Log`
    - `Laminas\Mvc\I18n`
    - `Laminas\Paginator`
    - `Laminas\Router`
    - `Laminas\Validator`
- Update your `config/development.config.php` and
  `config/development.config.php.dist` files:
  - Remove from the modules list:
    - `LaminasTool`
- Remove `composer.lock`
- Remove, recursively, the `vendor/` subdirectory
- Execute `composer install`

> ### Development Mode
>
> Prior to 1.5 and running the upgrade script or following the upgrade
> instructions from above, Laminas API Tools used laminas-development-mode v2 releases,
> which relied on the Console &lt;-&gt; MVC integration present by default in
> laminas-mvc v2 releases.
>
> laminas-development-mode v3 operates differently, however, and instead ships as
> a Composer vendor binary, with no additional requirements. Invocation is now:
>
> ```bash
> $ ./vendor/bin/laminas-development-mode enable
> ```
>
> and
>
> ```bash
> $ ./vendor/bin/laminas-development-mode disable
> ```
>
> You can also query for status:
>
> ```bash
> $ ./vendor/bin/laminas-development-mode status
> ```

Configuration
-------------

Since this particular module is responsible for providing APIs and the Laminas API Tools Admin UI, it has a
significant amount of configuration that it requires in order to function in a development
environment. Since it is highly unlikely that developers would need to modify the system-level
configuration, it is omitted in this README, but can be found [within the
repository](https://github.com/laminas-api-tools/api-tools-admin/tree/master/config/module.config.php).

Additionally, the module defines the following module-specific configuration,
under the top-level key `api-tools-admin`:

### Key: path_spec

By default, api-tools-admin will create new Laminas API Tools modules using
[PSR-0](http://www.php-fig.org/psr/psr-0/) directory structure. You can switch
to [PSR-4](http://www.php-fig.org/psr/psr-4/) using the
`api-tools-admin.path_spec` configuration, which accepts one of the following
values:

- `Laminas\ApiTools\Admin\Model\ModulePathSpec::PSR_0` ('psr-0')
- `Laminas\ApiTools\Admin\Model\ModulePathSpec::PSR_4` ('psr-4')

Routes
------

This module exposes HTTP accessible API endpoints and static assets.

API Endpoints
-------------

All routes are prefixed with `/api-tools` by default.

### api/api-tools-version

- Since 1.5.1

Returns the current Laminas API Tools version if it can be discovered, and the string
`@dev` otherwise. The payload is in the `version` key:

```json
{
    "version": "1.4.0"
}
```

- `Accept`: `application/json`

- `Content-Type`: `application/json`

- Methods: `GET`

- Errors: none

### api/config

This endpoint is for examining the application configuration, and providing
overrides of individual values in it. All overrides are written to a single
file, `config/autoload/development.php`; you can override that location in your
configuration via the `api-tools-configuration.config-file` key.

- `Accept`: `application/json`, `application/vnd.laminascampus.v1.config+json`

  `application/json` will deliver representations as a flat array of key/value pairs,
  with the keys being dot-separated values, just as you would find in INI.

  `application/vnd.laminascampus.v1.config+json` will deliver the configuration as a tree.

- `Content-Type`: `application/json`, `application/vnd.laminascampus.v1.config+json`

  `application/json` indicates you are sending key/value pairs, with keys being dot-separated
  values, as you would find in INI files.

  `application/vnd.laminascampus.v1.config+json` indicates you are sending a nested array/object of
  configuration.

- Methods: `GET`, `PATCH`

- Errors: `application/problem+json`

### api/config/module?module={module name}

This operates exactly like the `api/config` endpoint, but expects a known
module name. When provided, it allows you to introspect and manipulate the
configuration file for that module.

### api/authentication

This REST endpoint is for creating, updating, and deleting the authentication
configuration for your application. It uses the [authentication
resource](#authentication).

- `Accept`: `application/json`

  Returns an [authentication resource](#authentication) on success.

- `Content-Type`: `application/json`

  Expects an [authentication resource](#authentication) with all details
  necessary for creating new, or updating existing, HTTP authentication.

- HTTP methods: `GET`, `POST`, `PATCH`, `DELETE`

  `GET` returns a `404` response if no authentication has previously been setup.
  `POST` will return a `201` response on success. `PATCH` will return a `200`
  response on success. `DELETE` will return a `204` response on success.

- Errors: `application/problem+json`

### api/authentication[/:authentication_adapter] (API V2)

This REST endpoint is for fetching and updating the authentication
adapters to be used in Laminas API Tools. It uses the [authentication
resource ver. 2](#authentication2).

This endpoint is only available for API **version 2**. You need to pass the
following mediatype in the Appect header:

```
Accept: application/vnd.api-tools.v2+json
```

- `Accept`: `application/json`

  Returns an [authentication resource ver. 2](#authentication2) on success.

- Content-Type: `application/json`

  Expects an [authentication resource ver. 2](#authentication2) with all details
  necessary for creating new, or updating existing, HTTP authentication.

- HTTP methods: `GET`, `POST`, `PUT`, `DELETE`

  `GET` returns a `404` response if no authentication adapter has previously
  been setup. `POST` will return a `201` response on success. `PUT` will return
  a `200` response on success. `DELETE` will return a `204` response on success.


### api/module/:name/authentication?version=:version (API V2)

This REST endpoint is for fetching and updating the authentication
mapping for a specific API (module) and version, if specified.

This endpoint is only available for API **version 2**. You need to pass the
following mediatype in the Appect header:

```
Accept: application/vnd.api-tools.v2+json
```

- `Accept`: `application/json`

  Returns an { "authentication" : value } on success.

- Content-Type: `application/json`

  Expects a JSON with **authentication** value containing the authentication
  adapter name.

- HTTP methods: `GET`, `PUT`, `DELETE`

  `GET` will return an { "authentication" : value } response. If no
  authentication adapter exists the value will be false.

  `PATCH` will return a `200` response on success, along with the updated
  authentication value.

  `DELETE` will return a `204` response on success.

### api/module/:name/authorization?version=:version

This REST endpoint is for fetching and updating the authorization
configuration for your application. It uses the [authorization
resource](#authorization).

- `Accept`: `application/json`

  Returns an [authorization resource](#authorization) on success.

- Content-Type: `application/json`

  Expects an [authorization resource](#authorization) with all details
  necessary for specifying authorization rules.

- HTTP methods: `GET`, `PATCH`

  `GET` will always return an entity; if no configuration existed previously
  for the module, or if any given service at the given version was not listed
  in the configuration, it will provide the default values.

  `PATCH` will return a `200` response on success, along with the updated
  entity.

- Errors: `application/problem+json`

### api/db-adapter[/:adapter_name]

This REST endpoint is for creating, updating, and deleting named `Laminas\Db`
adapters; it uses the [db-adapter resource](#db-adapter).

- `Accept`: `application/json`

  Returns a [db-adapter resource](#db-adapter) on success.

- `Content-Type`: `application/json`

  Expects [db-adapter resource](#db-adapter) with all details necessary for
  creating or updating a DB connection.

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`, `PATCH`, `DELETE`

- Errors: `application/problem+json`

### api/module.enable

This endpoint will Laminas API Tools-enable (Apigilify?) an existing module.

- `Accept`: `application/json`

  Returns a [Module resource](#module) on success.

- `Content-Type`: `application/json`

  Expects an object with the property "module" describing an existing Laminas module.

- Methods: `PUT`

- Errors: `application/problem+json`

The request payload should hav ethe following structure:

```JSON
{
    "module": "Status"
}
```

### api/validators

This endpoint provides a sorted list of all registered validator plugins; the
use case is for building a drop-down of available plugins when creating an
input filter for a service. Any validator present in the Laminas `ValidatorPluginManager`
service will be represented.

- `Accept`: `application/json`

  Returns an `application/json` response on success.

- Methods: `GET`

- Errors: `application/problem+json`

The response payload for a successful request has the following format:

```JSON
{
  "validators": [
    "list",
    "of",
    "validators"
  ]
}
```

### api/versioning

This endpoint is for adding a new version to an existing API. If no version is
passed in the payload, the version number is simply incremented.

- `Accept`: `application/json`

  Returns a JSON structure on success, an API-Problem payload on error.

- `Content-Type`: `application/json`

  Expects an object with the property "module", providing the name of a Laminas,
  Laminas API Tools-enabled module; optionally, a "version" property may also be
  provided to indicate the specific version string to use.


- Methods: `PATCH`

- Errors: `application/problem+json`

The request payload should have the following structure:

```JSON
{
    "module": "Status",
    "version": 10
}
```

On success, the service returns the followings structure:

```JSON
{
    "success": true,
    "version": "version string or integer"
}
```

### api/module[/:name]

This is the canonical endpoint for [Module resources](#module).

- `Accept`: `application/json`

  Returns either a single [Module resource](#module) (when a `name` is provided)
  or a collection of Module resources (when no `name` is provided) on success.

- `Content-Type`: `application/json`

  Expects an object with the property "name" describing the module to create.

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`

- Errors: `application/problem+json`

When creating a new API module, use the following request payload:

```JSON
{
    "name": "Status"
}
```

### api/module/:name/rpc[/:controller_service_name]

This is the canonical endpoint for [RPC resources](#rpc).

- `Accept`: `application/json`

  Returns either a single [RPC resource](#rpc) (when a `controller_service_name`
  is provided) or a collection of RPC resources (when no
  `controller_service_name` is provided) on success.

- `Content-Type`: `application/json`

  Expects an object with the property "service_name" describing the endpoint to
  create.

  You may also provide any other options listed in the [RPC resource](#rpc).

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`, `PATCH`

- The query string variable `version` may be passed to the collection to filter
  results by version: e.g., `/admin/api/module/:name/rpc?version=2`.

- Errors: `application/problem+json`

The minimal request payload necessary will have the following structure:

```JSON
{
    "service_name": "Status"
}
```

### api/module/:name/rpc/:controller_service_name/inputfilter[/:input_filter_name]

This service is for creating, updating, and deleting named [input filters](#inputfilter)
associated with a given RPC service.

- `Accept`: `application/json`

  Returns either a single [input filter](#inputfilter) (when an
  `input_filter_name` is provided) or a collection of input filters (when no
  `input_filter_name` is provided) on success. Typically, only one input
  filter will be associated with a given RPC service.

  Input filters returned will also compose a property `input_filter_name`,
  which is the identifier for the given input filter.

- `Content-Type`: `application/json`

  Expects an [input filter](#inputfilter).

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`, `PUT`, `DELETE`

- Errors: `application/problem+json`

### api/module/:name/rest[/:controller_service_name]

This is the canonical endpoint for [REST resources](#rest).

Can be used for any type of REST resource, including DB-Connected.

DB-Connected resources expect the following additional properties (and will
return them as well):

- `adapter_name`: A named DB adapter service.
- `table_name`: The database table associated with this service.
- `hydrator_name`: Optional; the name of a hydrator service used to hydrate rows
  returned by the database; defaults to `ArraySerializable`.
- `table_service`: Optional; this is auto-generated by default, but an alternate
  TableGateway service may be provided.

- `Accept`: `application/json`

  Returns either a single [REST resource](#rest) (when a `controller_service_name`
  is provided) or a collection of REST resources (when no
  `controller_service_name` is provided) on success.

- `Content-Type`: `application/json`

  Expects an object with the property `resource_name` describing the REST service to create.

  You may also provide any other options listed in the [REST resource](#rest).

- Collection Methods: `GET`, `POST`, `DELETE`

- Resource Methods: `GET`, `PATCH`

- The query string variable `version` may be passed to the collection to filter
  results by version: e.g., `/admin/api/module/:name/rest?version=2`.

- Errors: `application/problem+json`

The minimum structure for creating a new REST service will appear as follows:

```JSON
{
    "resource_name": "Status"
}
```

### api/package

This endpoint is for building a deploy package for APIs.

- `Accept`: `application/json`

  Returns a JSON structure on success, an API-Problem payload on error.

- `Content-Type`: `application/json`

  Expects an object with the property "format", for the file format
  ZIP, TAR, TGZ, and ZPK; an "apis" property with a list of the API to
  include in the package; a "composer" property that specify if execute
  composer or not and an optional "config" property containing the path
  to an application config folder to be used in the package.


- Methods: `GET`, `POST`

- Errors: `application/problem+json`

The request payload for `POST` should have the following structure:

```JSON
{
    "format": "the file format to be used for the package",
    "apis" : {
        "Test": true
    },
    "composer": true,
    "config": "the config path to be used in the package"
}
```

On success, the service returns the followings structure:

```JSON
{
    "token": "a random token string",
    "format": "the file format used for the package"
}
```

The fields of this response can be used in the `GET` method to download
the package file. Basically, the token is a temporary file name stored in
the system temporary folder (`/tmp` in GNU/Linux).

The request payload for `GET` should have the following structure:

```
GET /api/package?token=xxx&format=yyy
```

On success, the service returns the file as `application/octet-stream`
content type.



API Models
----------

The following is a list of various models either returned via the API endpoints listed above, or
expected for the request bodies.

### authentication

#### HTTP Basic authentication:

```JSON
{
    "accept_schemes": [ "basic" ],
    "realm": "The HTTP authentication realm to use",
    "htpasswd": "path on filesystem to htpasswd file"
}
```

#### HTTP Digest authentication:

```JSON
{
    "accept_schemes": [ "digest" ],
    "realm": "The HTTP authentication realm to use",
    "htdigest": "path on filesystem to htdigest file",
    "nonce_timeout": "integer; seconds",
    "digest_domains": "Space-separated list of URIs under authentication"
}
```

#### OAuth2 authentication:

```JSON
{
    "dsn": "PDO DSN of database containing OAuth2 schema",
    "username": "Username associated with DSN",
    "password": "Password associated with DSN",
    "route_match": "Literal route to match indicating where OAuth2 login/authorization exists"
}
```

### authentication2

#### HTTP Basic authentication:

```JSON
{
    "name" : "Name of the authentication adapter",
    "type": "basic",
    "realm": "The HTTP authentication realm to use",
    "htpasswd": "Path on filesystem to htpasswd file"
}
```

#### HTTP Digest authentication:

```JSON
{
    "name" : "Name of the authentication adapter",
    "type": "digest",
    "realm": "The HTTP authentication realm to use",
    "digest_domains": "Space-separated list of URIs under authentication",
    "nonce_timeout": "integer; seconds",
    "htdigest": "Path on filesystem to htdigest file"
}
```

#### OAuth2 authentication (with PDO):

```JSON
{
    "name" : "Name of the authentication adapter",
    "type": "oauth2",
    "oauth2_type" : "pdo",
    "oauth2_route" : "Literal route to match indicating where OAuth2 login/authorization exists",
    "oauth2_dsn": "PDO DSN of database containing OAuth2 schema",
    "oauth2_username": "Username associated with DSN (optional)",
    "oauth2_password": "Password associated with DSN (optional)",
    "oauth2_options": "(optional)"
}
```

#### OAuth2 authentication (with MongoDB):

```JSON
{
    "name" : "Name of the authentication adapter",
    "type": "oauth2",
    "oauth2_type" : "mongo",
    "oauth2_route" : "Literal route to match indicating where OAuth2 login/authorization exists",
    "oauth2_dsn": "MongoDB DSN of database containing OAuth2 documents",
    "oauth2_database": "Database name",
    "oauth2_locator_name": "SomeServiceName class (optional)",
    "oauth2_options": "(optional)"
}
```

### authorization

```JSON
{
    "Rest\Controller\Service\Name::__resource__": {
        "GET": bool,
        "POST": bool,
        "PUT": bool,
        "PATCH": bool,
        "DELETE": bool
    },
    "Rest\Controller\Service\Name::__collection__": {
        "GET": bool,
        "POST": bool,
        "PUT": bool,
        "PATCH": bool,
        "DELETE": bool
    },
    "Rpc\Controller\Service\Name::actionName": {
        "GET": bool,
        "POST": bool,
        "PUT": bool,
        "PATCH": bool,
        "DELETE": bool
    }
}
```

REST services have an entry for each of their entity and collection instances.
RPC services have an entry per action name that is exposed (this will typically
only be one). Each service has a list of HTTP methods, with a flag. A `false`
value indicates that no authorization is required; a `true` value indicates that
authorization is required.

> **Note**: If the `deny_by_default` flag is set in the application, then the
> meaning of the flags is reversed; `true` then means the method is public,
> `false` means it requires authentication.

### db-adapter

```JSON
{
    "adapter_name": "Service name for the DB adapter",
    "database": "Name of the database",
    "driver": "Driver used to make the connection"
}
```

Additionally, any other properties used to create the `Laminas\Db\Adapter\Adapter`
instance may be composed: e.g., "username", "password", etc.

### inputfilter

```JSON
{
    "input_name": {
        "name": "name of the input; should match key of object",
        "validators": [
            {
                "name": "Name of validator service",
                "options": {
                    "key": "value pairs to specify behavior of validator"
                }
            }
        ]
    }
}
```

An input filter may contain any number of inputs, and the format follows that
used by `Laminas\InputFilter\Factory` as described in the [Laminas input filter documentation]
(https://getlaminas.org/manual/2.3/en/modules/laminas.input-filter.intro.html).

Currently, we do not allow nesting input filters.

### module

```JSON
{
    "name": "normalized module name",
    "namespace": "PHP namespace of the module",
    "is_vendor": "boolean value indicating whether or not this is a vendor (3rd party) module",
    "versions": [
        "Array",
        "of",
        "available versions"
    ]
}
```

Additionally, the `module` resource composes relational links for [RPC](#rpc)
and [REST](#rest) resources; these use the relations "rpc" and "rest",
respectively.

### rpc

```JSON
{
    "controller_service_name": "name of the controller service; this is the identifier, and required",
    "accept_whitelist": [
        "(Optional)",
        "List",
        "of",
        "whitelisted",
        "Accept",
        "mediatypes"
    ],
    "content_type_whitelist": [
        "(Optional)",
        "List",
        "of",
        "whitelisted",
        "Content-Type",
        "mediatypes"
    ],
    "http_options": [
        "(Required)",
        "List",
        "of",
        "allowed",
        "Request methods"
    ],
    "input_filter": "(Optional) Present in returned RPC services, when one or more input filters are present; see the inputfilter resource for details",
    "route_match": "(Required) String indicating Segment route to match",
    "route_name": "(Only in representation) Name of route associated with endpoint",
    "selector": "(Optional) Content-Negotiation selector to use; Json by default"
}
```

### rest

```JSON
{
    "controller_service_name": "name of the controller service; this is the identifier, and required",
    "accept_whitelist": [
        "(Optional)",
        "List",
        "of",
        "whitelisted",
        "Accept",
        "mediatypes"
    ],
    "adapter_name": "(Only in DB-Connected resources) Name of Laminas\\DB adapter service used for this resource",
    "collection_class": "(Only in representation) Name of class representing collection",
    "collection_http_options": [
        "(Required)",
        "List",
        "of",
        "allowed",
        "Request methods",
        "on collections"
    ],
    "collection_query_whitelist": [
        "(Optional)",
        "List",
        "of",
        "whitelisted",
        "query string parameters",
        "to pass to resource for collections"
    ],
    "content_type_whitelist": [
        "(Optional)",
        "List",
        "of",
        "whitelisted",
        "Content-Type",
        "mediatypes"
    ],
    "entity_class": "(Only in representation) Name of class representing resource entity",
    "entity_identifier_name": "(Optional) Name of entity field representing the identifier; defaults to 'id'",
    "hydrator_name": "(Only in DB-Connected resources) Name of Laminas\\Stdlib\\Hydrator service used for this resource",
    "route_identifier_name": "(Optional) Name of route parameter representing the resource identifier; defaults to resource_name + _id",
    "input_filter": "(Optional) Present in returned REST services, when one or more input filters are present; see the inputfilter resource for details",
    "module": "(Only in representation) Name of module in which resource resides",
    "page_size": "(Optional) Integer representing number of entities to return in a given page in a collection; defaults to 25",
    "page_size_param": "(Optional) Name of query string parameter used for pagination; defaults to 'page'",
    "resource_class": "(Only in representation) Name of class representing resource handling operations",
    "resource_http_options": [
        "(Required)",
        "List",
        "of",
        "allowed",
        "Request methods",
        "on individual resources"
    ],
    "route_match": "(Optional) String indicating Segment route to match; defaults to /resource_name[/:route_identifier_name]",
    "route_name": "(Only in representation) Name of route associated with api service",
    "selector": "(Optional) Content-Negotiation selector to use; HalJson by default",
    "table_name": "(Only in DB-Connected resources) Name of database table used for this resource",
    "table_service": "(Only in DB-Connected resources) Name of TableGateway service used for this resource"
}
```

Laminas Events
----------

### Listeners

#### Laminas\ApiTools\Admin\Module

This listener is attached to `MvcEvent::EVENT_RENDER` at priority `100`.  It is responsible for
conditionally attaching a listener depending on if the controller service result is that of
an _entity_ or that of a _collection_.  If either is detected, the listener is attached
to the `Laminas\ApiTools\Hal\Plugin\Hal` events `renderEntity` and `renderCollection.entity`, which
ensures they will be dispatched when the HAL plugin has an opportunity to start rendering.

Laminas Services
------------

### Models

Many of the model services provided by `api-tools-admin` either deal with the generation and
modification of PHP code, or the generation and modification of PHP based configuration files.

- `Laminas\ApiTools\Admin\Model\AuthenticationModel` - responsible for creating and modifying the
  authentication specific configuration of HTTP Basic, HTTP Digest and OAuth2 strategies. Sensitive
  information will be written to local configuration files while structural information is
  written to global and module files.
- `Laminas\ApiTools\Admin\Model\AuthorizationModelFactory` - responsible for writing the authorization
  specific details (the ACL matrix of allow/disallow rules) to the module configuration file.
- `Laminas\ApiTools\Admin\Model\ContentNegotiationModel` - responsible for writing custom
  content-negotiation selectors to the global configuration file.
- `Laminas\ApiTools\Admin\Model\ContentNegotiationResource` - REST resource that consumes the
  `ContentNegotiationModel` in order to expose an API endpoint for content-negotiation
  configuration management.
- `Laminas\ApiTools\Admin\Model\DbAdapterModel` - responsible for writing database adapter specific
  configuration between application level global and local configuration files. Sensitive
  information is written to local configuration files.
- `Laminas\ApiTools\Admin\Model\DbAdapterResource` - REST resource that consumes the `DbAdapterModel`
  in order to expose an API endpoint for database adapter configuration management.
- `Laminas\ApiTools\Admin\Model\DbConnectedRestServiceModel` - responsible for writing the required
  configuration information necessary to expose a database table as a REST resource.
- `Laminas\ApiTools\Admin\Model\DocumentationModel` - responsible for writing a special named
  file in the module's configuration directory that will contain all custom API documentation
  for requests, responses, and all other documentable elements of an API.
- `Laminas\ApiTools\Admin\Model\InputFilterModel` - responsible for writing the input filter
  specification configuration for each module.
- `Laminas\ApiTools\Admin\Model\FiltersModel` - responsible for providing, through the API, a list of
  built-in filters and their metadata.
- `Laminas\ApiTools\Admin\Model\HydratorsModel` - responsible for configuring and managing the
  global list of hydrator service names.
- `Laminas\ApiTools\Admin\Model\ModuleModel` - responsible for aggregating module information including
  REST and RPC services and exposing this information through the API.  Additionally, when creating
  a new module, this will create the code artifacts necessary for an Laminas API Tools-enabled module.
- `Laminas\ApiTools\Admin\Model\ModuleResource` - responsible for exposing the `ModuleModel` as a
  REST resource in the Laminas API Tools API.
- `Laminas\ApiTools\Admin\Model\RestServiceModel` - responsible for presenting REST services, as they
  are defined in `api-tools-rest` in a way that can be created and modified, to be used in the Admin UI.
- `Laminas\ApiTools\Admin\Model\RestServiceResource` - responsible for consuming `RestServiceModel` and
  exposing this model as a REST resource in the Laminas API Tools API.
- `Laminas\ApiTools\Admin\Model\RestServiceModelFactory` - responsible for creating `RestServiceModel`s.
- `Laminas\ApiTools\Admin\Model\RpcServiceModel` - responsible for presenting RPC services, as they are
  defined in `api-tools-rpc` in a way that can be created and modified, to be used in the Admin UI.
- `Laminas\ApiTools\Admin\Model\RpcServiceResource` - responsible for consuming `RpcServiceModel`s and
  exposing this model as a REST resource in the Laminas API Tools API.
- `Laminas\ApiTools\Admin\Model\RpcServiceModelFactory` - responsible for creating `RpcServiceModel`s.
- `Laminas\ApiTools\Admin\Model\ValidatorsModel` - responsible for providing, through the API, a list of
  available validators.
- `Laminas\ApiTools\Admin\Model\ValidatorMetadataModel` - responsible for providing metadata about
  validators provided through, and in conjunction with, the `ValidatorModel` and validator API.
- `Laminas\ApiTools\Admin\Model\VersioningModel` - responsible for modeling the workflow and module
  code creation artifacts that are required to provide a new version of a particular Laminas API Tools-based
  REST or RPC service.
- `Laminas\ApiTools\Admin\Model\ModuleVersioningModelFactory` - responsible for creating `ModuleVersioningModel`s.
