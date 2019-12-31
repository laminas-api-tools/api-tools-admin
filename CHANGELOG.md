# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.8.0 - 2019-04-04

### Added

- [zfcampus/zf-apigility-admin#393](https://github.com/zfcampus/zf-apigility-admin/pull/393) adds the documentation key "identifier" to the `DocumentationModel` and
  allowed documentation keys; the key is used in conjunction with the API
  Blueprint "identifier" and Swagger "operationId" fields.

### Changed

- [zfcampus/zf-apigility-admin#398](https://github.com/zfcampus/zf-apigility-admin/pull/398) changes how paths are globbed to use `realpath()` when providing a path
  to `glob()`, fixing an issue when used on IBM i platforms.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.7.0 - 2018-12-11

### Added

- [zfcampus/zf-apigility-admin#396](https://github.com/zfcampus/zf-apigility-admin/pull/396) adds support for laminas-hydrator v3 releases, and maintains compatibility
  with v1 and v2.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.6.0 - 2018-05-08

### Added

- [zfcampus/zf-apigility-admin#373](https://github.com/zfcampus/zf-apigility-admin/pull/373) adds a new optional argument to `ModuleModel::createModule()`, `int $version = 1`. This
  option allows a newly created module to start at a version greater than 1 if desired.

### Changed

- [zfcampus/zf-apigility-admin#392](https://github.com/zfcampus/zf-apigility-admin/pull/392) updates all dependency constraints to pin to versions that
  support PHP 7.2 where possible.

### Deprecated

- Nothing.

### Removed

- [zfcampus/zf-apigility-admin#392](https://github.com/zfcampus/zf-apigility-admin/pull/392) removes support for HHVM.

### Fixed

- Nothing.

## 1.5.13 - 2017-12-14

### Added

- [zfcampus/zf-apigility-admin#383](https://github.com/zfcampus/zf-apigility-admin/pull/383) adds official
  support for PHP 7.1 and 7.2 by ensuring we test against both versions during
  continuous integration. Tests passed with no additional code changes.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#382](https://github.com/zfcampus/zf-apigility-admin/pull/382) updates the
  signatures of each of the following listed filters to be compatible with
  `Laminas\InputFilter\BaseInputFilter::isValid()` across all compatible versions
  of laminas-inputfilter, eliminating an error when using laminas-inputfilter 2.8+:

  - `AuthorizationInputFilter`
  - `DocumentationInputFilter`
  - `InputFilterInputFilter`

## 1.5.12 - 2017-12-14

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#381](https://github.com/zfcampus/zf-apigility-admin/pull/381) updates the
  signature of `PostInputFilter::isValid()` to be compatible with
  `Laminas\InputFilter\BaseInputFilter::isValid()` across all compatible versions
  of laminas-inputfilter, eliminating an error when using laminas-inputfilter 2.8+.

## 1.5.11 - 2017-11-14

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#378](https://github.com/zfcampus/zf-apigility-admin/pull/378) modifies the
  package requirements to exclude api-tools-configuration v1.3.1, as that version has a
  backwards-incompatible change that prevents creation of services via the
  admin.

## 1.5.10 - 2017-08-14

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#370](https://github.com/zfcampus/zf-apigility-admin/pull/370) provides a fix
  to the `RpcServiceModel::fetchAll()` method that allows it to work with PSR-4
  structured modules.

## 1.5.9 - 2016-10-12

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#369](https://github.com/zfcampus/zf-apigility-admin/pull/369) updates the
  migration script's `public/index.php` changes such that the stub generated now
  will vary the script location for `laminas-development-mode` based on whether or
  not a Windows operating system is detected.

## 1.5.8 - 2016-10-11

### Added

- [zfcampus/zf-apigility-admin#363](https://github.com/zfcampus/zf-apigility-admin/pull/363) adds an entry
  for `Laminas\Validator\Uuid` to the validator metadata.
- [zfcampus/zf-apigility-admin#368](https://github.com/zfcampus/zf-apigility-admin/pull/368) updates the 
  `bin/api-tools-upgrade-to-1.5` script to also inject a stub into the
  `public/index.php` that will intercept `php public/index.php development
  [enable|disable]` commands, and proxy them to the v3 laminas-development-mode
  tooling.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#365](https://github.com/zfcampus/zf-apigility-admin/pull/365) updates the 
  logic in the `DbAutodiscoveryModel` to catch and report exceptions due to
  metadata discovery issues (typically invalid character sets) that were
  previously returning an empty list, providing better diagnostic details to
  end-users.

## 1.5.7 - 2016-08-14

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#362](https://github.com/zfcampus/zf-apigility-admin/pull/362) adds an entry
  to remove `Laminas\ApiTools\Provider` from the module list in the
  `api-tools-update-to-1.5` script. The package does not need to be listed as a
  module, as Composer will autoload all interfaces it defines.

## 1.5.6 - 2016-08-14

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#361](https://github.com/zfcampus/zf-apigility-admin/pull/361) updates the
  `ModuleModel` to vary the contents of a generated `module.config.php` based on
  the short-array notation configuration setting.
- This release updates the following dependencies to the listed minimum
  supported versions:
  - laminas-api-tools/api-tools-admin-ui: 1.3.7
  - zfcampus/zf-configuration: 1.2.1

## 1.5.5 - 2016-08-12

### Added

- [zfcampus/zf-apigility-admin#358](https://github.com/zfcampus/zf-apigility-admin/pull/358) adds
  documentation for the `api-tools-admin.path_spec` configuration value to
  both the README and the module configuration file.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#360](https://github.com/zfcampus/zf-apigility-admin/pull/360) fixes how the
  `ModuleModel` generates configuration, allowing it to generate short array
  syntax. The behavior is configurable using the
  `api-tools-configuration.enable_short_array` configuration value.

## 1.5.4 - 2016-08-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#357](https://github.com/zfcampus/zf-apigility-admin/pull/357) fixes an issue
  with detection of module versions when using Laminas API Tools-generated PSR-4
  modules.

## 1.5.3 - 2016-08-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#356](https://github.com/zfcampus/zf-apigility-admin/pull/356) fixes a fatal
  error when calling the versioning API, due to providing the
  `VersioningController` with an incorrect versioning model factory.
- [zfcampus/zf-apigility-admin#356](https://github.com/zfcampus/zf-apigility-admin/pull/356) fixes issues
  when versioning API modules that are in PSR-4 layout. The `ModuleModel` now
  autodiscovers which layout (PSR-0 or PSR-4) is used by a given module.

## 1.5.2 - 2016-08-10

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#354](https://github.com/zfcampus/zf-apigility-admin/pull/354) updates the
  upgrade script to add dependencies required by Laminas API Tools 1.3 and earlier
  skeletons. These changes include:
  - adding laminas/laminas-mvc-i18n as a dependency
  - adding the `Laminas\I18n` and `Laminas\Mvc\I18n` modules to `config/modules.config.php`

## 1.5.1 - 2016-08-10

### Added

- [zfcampus/zf-apigility-admin#353](https://github.com/zfcampus/zf-apigility-admin/pull/353) adds the
  `api-tools-version` API, to allow reporting to the UI the current Laminas API Tools
  skeleton version. It returns the value of `ApiTools\VERSION` if defined, and
  `@dev` if not.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.5.0 - 2016-08-09

### Added

- [zfcampus/zf-apigility-admin#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) updates the
  component to be forwards compatible with Laminas component v3 releases,
  while retaining support for v2 releases. This includes supporting both v2 and
  v3 versions of factory invocation, and triggering event listeners using syntax
  that works on both v2 and v3 releases of laminas-eventmanager, amonst other
  changes.

- [zfcampus/zf-apigility-admin#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) adds a script
  to assist users in updating existing Laminas API Tools applications to use Laminas
  Framework component v3 releases:

  ```bash
  $ ./vendor/bin/api-tools-upgrade-to-1.5 -h
  ```

  In most cases, you can call it without arguments. Running the script updates
  your `composer.json` to remove several entries, update others, and add some;
  it then updates your list of modules, and then installs dependencies for you.

  If you need to update manually for any reason, you will need to
  [follow the steps in the README](README.md#upgrading-to-v3-laminas-framework-components-from-1.5).

- [zfcampus/zf-apigility-admin#321](https://github.com/zfcampus/zf-apigility-admin/pull/321) adds a
  `patchList()` stub to the REST resource class template, so that it's present
  by default.

- [zfcampus/zf-apigility-admin#327](https://github.com/zfcampus/zf-apigility-admin/pull/327) adds support
  for working with modules that are in PSR-4 directory format. While the admin
  still does not create PSR-4 modules, it will now correctly interact with those
  that you manually convert to PSR-4.

- [zfcampus/zf-apigility-admin#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) extracts
  listeners previously defined in the `Module` class into their own classes.
  These include:

  - `Laminas\ApiTools\Admin\DisableHttpCacheListener`, which listens on the
    `MvcEvent::EVENT_FINISH` event at high priority in order to return cache
    busting headers in the returned response.
  - `Laminas\ApiTools\Admin\EnableHalRenderCollectionsListener`, which listens on
    the `MvcEvent::EVENT_ROUTE` event at low priority in order to set the
    "render collections" flag on api-tools-hal's `Hal` plugin if a controller from the
    module is matched.
  - `Laminas\ApiTools\Admin\InjectModuleResourceLinksListener`, which listens on the
    `MvcEvent::EVENT_RENDER` event at high priority in order to attach listeners to
    events on the api-tools-hal `Hal` plugin. These listeners were also previously
    defined in the `Module` class, and are now part of this new listener, as it
    aggregates some state used by each.
  - `Laminas\ApiTools\Admin\NormalizeMatchedControllerServiceNameListener`, which
    listens on the `MvcEvent::EVENT_ROUTE` at low priority in order to
    normalize the controller service name provided via the URI to a FQCN.
  - `Laminas\ApiTools\Admin\NormalizeMatchedInputFilterNameListener`, which listens
    on the `MvcEvent::EVENT_ROUTE` at low priority in order to normalize the
    input filter name provided via the URI to a FQCN.

- [zfcampus/zf-apigility-admin#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) extracts
  service factories previously defined in the `Module` class into their own
  classes. These include:
  - `Laminas\ApiTools\Admin\Model\AuthenticationModelFactory`
  - `Laminas\ApiTools\Admin\Model\AuthorizationModelFactory`
  - `Laminas\ApiTools\Admin\Model\ContentNegotiationModelFactory`
  - `Laminas\ApiTools\Admin\Model\ContentNegotiationResourceFactory`
  - `Laminas\ApiTools\Admin\Model\DbAdapterModelFactory`
  - `Laminas\ApiTools\Admin\Model\DbAdapterResourceFactory`
  - `Laminas\ApiTools\Admin\Model\DbAutodiscoveryModelFactory`
  - `Laminas\ApiTools\Admin\Model\DoctrineAdapterModelFactory`
  - `Laminas\ApiTools\Admin\Model\DoctrineAdapterResourceFactory`
  - `Laminas\ApiTools\Admin\Model\DocumentationModelFactory`
  - `Laminas\ApiTools\Admin\Model\FiltersModelFactory`
  - `Laminas\ApiTools\Admin\Model\InputFilterModelFactory`
  - `Laminas\ApiTools\Admin\Model\ModuleModelFactory`
  - `Laminas\ApiTools\Admin\Model\ModulePathSpecFactory`
  - `Laminas\ApiTools\Admin\Model\ModuleResourceFactory`
  - `Laminas\ApiTools\Admin\Model\ModuleVersioningModelFactory`
  - `Laminas\ApiTools\Admin\Model\ModuleVersioningModelFactoryFactory`
  - `Laminas\ApiTools\Admin\Model\RestServiceModelFactory`
  - `Laminas\ApiTools\Admin\Model\RestServiceModelFactoryFactory`
  - `Laminas\ApiTools\Admin\Model\RestServiceResourceFactory`
  - `Laminas\ApiTools\Admin\Model\RpcServiceModelFactoryFactory`
  - `Laminas\ApiTools\Admin\Model\RpcServiceResourceFactory`
  - `Laminas\ApiTools\Admin\Model\ValidatorMetadataModelFactory`
  - `Laminas\ApiTools\Admin\Model\ValidatorsModelFactory`
  - `Laminas\ApiTools\Admin\Model\VersioningModelFactory`
  - `Laminas\ApiTools\Admin\Model\VersioningModelFactoryFactory`

- [zfcampus/zf-apigility-admin#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) extracts
  controller factories previously defined in the `Module` class into their own
  classes, and updates several factories that already existed. Factories that
  existed were updated to follow both the laminas-servicemanager v2 and v3
  signatures, to allow compatibility with both versions; as such, if you were
  extending these previously, you may potentially experience breakage due to
  signatures. The new classes include:
  - `Laminas\ApiTools\Admin\Controller\AuthenticationControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\AuthenticationTypeControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\AuthorizationControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\ConfigControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\DashboardControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\DbAutodiscoveryControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\DocumentationControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\FiltersControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\HydratorsControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\InputFilterControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\ModuleConfigControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\ModuleCreationControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\SourceControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\StrategyControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\ValidatorsControllerFactory`
  - `Laminas\ApiTools\Admin\Controller\VersioningControllerFactory`

- [zfcampus/zf-apigility-admin#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) exposes the
  module to laminas-component-installer.

### Deprecated

- Nothing.

### Removed

- [zfcampus/zf-apigility-admin#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) removes
  support for PHP 5.5.
- [zfcampus/zf-apigility-admin#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) removes the
  dependency on rwoverdijk/assetmanager, allowing usage of any tool that
  understands the same configuration (and, specifically, the
  `asset_manager.resolver_configs.paths` configuration directive). However, **this
  means that for those upgrading via simple `composer update`, you will also
  need to execute `composer require rwoverdijk/assetmanager` immediately for
  your application to continue to work.**

### Fixed

- [zfcampus/zf-apigility-admin#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) updates
  `Laminas\ApiTools\Admin\Controller\StrategyController` to accept a
  `ContainerInterface` to its constructor, instead of relying on auto-injection
  of a laminas-servicemanager instance via an initializer; this change removes
  deprecation notices from its usage of `getServiceLocator()` (it no longer
  calls that method), and documents the dependency explicitly. If you were
  extending this class previously, you may need to update your factory.
- [zfcampus/zf-apigility-admin#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) updates
  `Laminas\ApiTools\Admin\Model\DoctrineAdapterResource`'s contructor to make the
  second argument, `$loadedModules`, optional. If you were extending the class
  previously, you may need to update your signature.

## 1.4.3 - 2016-08-05

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#350](https://github.com/zfcampus/zf-apigility-admin/pull/350) updates the
  `Module` class to pull entities composed in `Laminas\ApiTools\Hal\Entity` instances via the
  `getEntity()` method of that class, if it exists (introduced in api-tools-hal 1.4).
  This change prevents api-tools-hal 1.4+ versions from emitting deprecation notices,
  and thus breaking usage of the admin API.

## 1.4.2 - 2016-06-28

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#344](https://github.com/zfcampus/zf-apigility-admin/pull/344) removes the
  `ServiceLocatorAwareInterface`, and updates factories for autodiscovery
  classes to inject their service locator instead. This change removes
  deprecation notices when using Laminas API Tools with the laminas-mvc 2.7+ series.

## 1.4.1 - 2016-01-26

### Added

- [zfcampus/zf-apigility-admin#329](https://github.com/zfcampus/zf-apigility-admin/pull/329) improved install instructions

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#320](https://github.com/zfcampus/zf-apigility-admin/pull/320) typo fixes on array_fill() usage

## 1.4.0 - 2015-09-22

### Added

- [zfcampus/zf-apigility-admin#317](https://github.com/zfcampus/zf-apigility-admin/pull/317) updates the component
  to use laminas-hydrator for hydrator functionality; this provides forward
  compatibility with laminas-hydrator, and backwards compatibility with
  hydrators from older versions of laminas-stdlib.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.3.2 - 2015-09-22

### Added

- [zfcampus/zf-apigility-admin#311](https://github.com/zfcampus/zf-apigility-admin/pull/311) updates the
  API to allow using custom authentication adapters (vs only OAuth2 or HTTP).
- [zfcampus/zf-apigility-admin#314](https://github.com/zfcampus/zf-apigility-admin/pull/314) provides a
  simple fix to the `DbAutodiscoveryModel` which allows using database views for
  DB-connected services.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-admin#316](https://github.com/zfcampus/zf-apigility-admin/pull/316) updates the
  laminas-stdlib dependency to reference `>=2.5.0,<2.7.0` to ensure hydrators
  will work as expected following extraction of hydrators to the laminas-hydrator
  repository.
- [zfcampus/zf-apigility-admin#316](https://github.com/zfcampus/zf-apigility-admin/pull/316) fixes the
  OAuth2 input filter to ensure it works correctly with the latest versions of
  laminas-inputfilter.
