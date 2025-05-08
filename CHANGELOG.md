# Changelog

## [Unreleased]

### Changed

- Requires `innmind/immutable:~5.14`
- `Innmind\Server\Status\Server::cpu()` now returns `Innmind\Immutable\Attempt<Innmind\Server\Status\Server\Cpu>`
- `Innmind\Server\Status\Server::memory()` now returns `Innmind\Immutable\Attempt<Innmind\Server\Status\Server\Memory>`

### Removed

- `Innmind\Server\Status\Exception\EmptyPathNotAllowed`
- `Innmind\Server\Status\Exception\BytesCannotBeNegative`
- `Innmind\Server\Status\Exception\EmptyCommandNotAllowed`
- `Innmind\Server\Status\Exception\OutOfBoundsPercentage`
- `Innmind\Server\Status\Exception\LowestPidPossibleIsOne`

## 4.1.1 - 2024-09-30

### Fixed

- Networks Volumes are now listed in the `Innmind\Server\Status\Server\Disk::volumes()` method

## 4.1.0 - 2023-09-23

### Added

- Support for `innmind/immutable:~5.0`

### Removed

- Support for PHP `8.1`

## 4.0.0 - 2023-01-29

### Changed

- `Innmind\Server\Status\ServerFactory::build()` now expects `Innmind\Server\Control\Server` as second argument and `Innmind\Server\Status\EnvironmentPath` as third argument

## 3.0.0 - 2022-01-23

### Added

- `Innmind\Server\Status\Server\Process\Pid::equals()`
- `Innmind\Server\Status\Server\Process\Pid::is()`
- `Innmind\Server\Status\Server\Disk\Volume\MountPoint::equals()`
- `Innmind\Server\Status\Server\Disk\Volume\MountPoint::is()`

### Changed

- `Innmind\Server\Status\Server\Processes::all()` now returns `Innmind\Immutable\Set<Innmind\Server\Status\Server\Process>`
- `Innmind\Server\Status\Server\Processes::get()` now returns `Innmind\Immutable\Maybe<Innmind\Server\Status\Server\Process>` instead of throwing an exception
- `Innmind\Server\Status\Server\Process::start()` now returns `Innmind\Immutable\Maybe<Innmind\TimeContinuum\PointInTime>`
- `Innmind\Server\Status\Server\Memory\Bytes::of()` now returns `Innmind\Immutable\Maybe<Innmind\Server\Status\Server\Memory\Bytes>` instead of throwing an exception
- `Innmind\Server\Status\Server\Disk::volumes()` now return `Innmind\Immutable\Set<Innmind\Server\Status\Server\Disk\Volume>`
- `Innmind\Server\Status\Server\Disk::get()` now return `Innmind\Immutable\Maybe<Innmind\Server\Status\Server\Disk\Volume>` instead of throwing an exception
- `Innmind\Server\Status\Server::cpu()` now returns `Innmind\Immutable\Maybe<Innmind\Server\Status\Server\Cpu>` instead of throwing an exception
- `Innmind\Server\Status\Server::memory()` now returns `Innmind\Immutable\Maybe<Innmind\Server\Status\Server\Memory>` instead of throwing an exception

### Removed

- `Innmind\Server\Status\Servers\Decorator\CacheMemory`
- `Innmind\Server\Status\Servers\Decorator\CacheLoadAverage`
- `Innmind\Server\Status\Servers\Decorator\CacheCpu`
- `Innmind\Server\Status\ServerFactory::__invoke()`
- `Innmind\Server\Status\Server\Memory\Bytes` public constants
- `Innmind\Server\Status\Server\Memory::wired()`
- Support for php 7
