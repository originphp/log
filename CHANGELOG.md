# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2021-01-04

### Fixed

- Fixed PHP 8.0 type errors with str_replace

### Changed

- Changed minimum PHP version to 7.3
- Change minimum PHPUnit to 9.2

## [1.2.2] - 2020-07-16

### Changed

- Changed FileEngine log file default permissions to `0664`

## [1.2.1] - 2020-05-16

### Added
- Added option to disable log rotation

## [1.2.0] - 2020-05-15

### Added
- Added PSR-3 Logger class
- Added log rotation to FileEngine

## [1.1.1] - 2020-05-05
### Fixed
- Fixed issue on windows machines caused by zombie code from deprecation

## [1.1.0] - 2019-11-06
### Changed
- Removed lock flag when writing to log

## [1.0.1] - 2019-10-16
### Fixed
- Fixed invalid argument when there are no logging configurations set

## [1.0.0] - 2019-10-14

### Changed
- change how email configuration works, settings are passed in log configuration

This component has been decoupled from the [OriginPHP framework](https://www.originphp.com/).