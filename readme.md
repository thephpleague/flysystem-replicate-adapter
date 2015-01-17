# Flysystem Adapter for Replication.

[![Author](http://img.shields.io/badge/author-@frankdejonge-blue.svg?style=flat-square)](https://twitter.com/frankdejonge)
[![Build Status](https://img.shields.io/travis/thephpleague/flysystem-replicate-adapter/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/flysystem-replicate-adapter)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/flysystem-replicate-adapter.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/flysystem-replicate-adapter/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/flysystem-replicate-adapter.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/flysystem-replicate-adapter)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/league/flysystem-replicate-adapter.svg?style=flat-square)](https://packagist.org/packages/league/flysystem-replicate-adapter)
[![Total Downloads](https://img.shields.io/packagist/dt/league/flysystem-replicate-adapter.svg?style=flat-square)](https://packagist.org/packages/league/flysystem-replicate-adapter)


## Installation

```bash
composer require league/flysystem-replicate-adapter
```

## Usage

```php
$source = new League\Flysystem\Adapter\AwsS3(...);
$replica = new League\Flysystem\Adapter\Local(...);
$adapter = new League\Flysystem\Replicate\ReplicateAdapter($source, $replica);
```
