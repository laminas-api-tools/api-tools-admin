# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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
