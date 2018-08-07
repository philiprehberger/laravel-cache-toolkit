# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2026-03-16

### Changed
- Standardize composer.json: add type, homepage, scripts
- Add Development section to README

## [1.0.1] - 2026-03-15

### Changed
- Add README badges

## [1.0.0] - 2026-03-05

### Added
- `CacheKeyBuilder` — static helper for building standardised cache keys (`make`, `forModel`, `forModelType`, `forList`, `forAnalytics`, `forUser`, `forDateRange`, `forPaginated`, `ttl`, `ttlCarbon`, `tags`, `prefix`, `supportsTags`)
- `CacheTagManager` — tag-aware cache operations with automatic fallback for drivers that do not support tags (`remember`, `put`, `get`, `has`, `forget`, `flush`, `flushType`, `flushTypes`, `getConfiguredTags`, `supportsTags`)
- `CacheToolkitServiceProvider` — auto-discovered Laravel service provider; registers `CacheTagManager` as a singleton and publishes config
- `CacheKey` and `CacheTag` facades
- `config/cache-toolkit.php` — publishable config for prefixes, TTL values, and tag groups
- Full PHPUnit 11 test suite via Orchestra Testbench
- GitHub Actions CI matrix for PHP 8.2/8.3/8.4 and Laravel 11/12
