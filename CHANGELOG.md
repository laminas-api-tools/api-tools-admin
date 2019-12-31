# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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
