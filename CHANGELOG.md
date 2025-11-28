# Changelog

All notable changes to `cloak-laravel` will be documented in this file.

## [0.2.1] - 2025-11-28

### Added
- New `LaravelEncryptor` implementing `EncryptorInterface` from cloak-php v0.2.0
- Resolver pattern integration via `Cloak::resolveUsing()` for seamless Laravel container integration
- Advanced usage documentation for extending Cloak via container bindings
- Test coverage for fresh instance creation and resolver functionality

### Changed
- **Updated to cloak-php v0.2.0** with full builder pattern support
- Refactored `CloakServiceProvider` to use optimal container binding strategy:
  - `Cloak` uses `bind()` for fresh instances (Octane-safe, prevents state pollution)
  - `StoreInterface` uses `singleton()` for shared storage
  - `EncryptorInterface` uses `singleton()` for stateless encryption service
- Simplified `CacheStorage` implementation - encryption now handled by `LaravelEncryptor`
- Updated `CacheStorage::put()` signature to remove `$ttl` parameter (now set via constructor)
- Now uses core package's helper functions instead of custom Laravel-specific helpers
- Replaced custom `EncryptedArrayStorage` with core `ArrayStore` + `LaravelEncryptor`

### Removed
- `EncryptedArrayStorage` class (superseded by core `ArrayStore` with `LaravelEncryptor`)
- Custom helper functions in `src/helpers.php` (now using core package helpers via resolver)
- TTL parameter from `StoreInterface::put()` method signature

### Fixed
- State pollution issues in Laravel Octane environments
- Filter and callback persistence across different Cloak instances

### Documentation
- Added comprehensive version compatibility section
- Documented architecture improvements and Octane-safe binding strategy
- Added advanced usage examples for container-based customization
- Clarified breaking changes from previous versions

## [0.2.0] - 2025-11-28

### Changed
- Updated dependency to `dynamik-dev/cloak-php: ^0.2`

## [0.1.0] - Initial Release

### Added
- Initial Laravel adapter for cloak-php
- Configuration file with persist mode support
- Cache storage implementation with Laravel's Crypt facade
- Helper functions for cloaking and uncloaking
- Facade support
- Service provider with automatic package discovery
