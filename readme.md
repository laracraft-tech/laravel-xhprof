<p align="center"><img src="https://i.imgur.com/nsWZks5.png" alt="Logo Laravel XHProf"></p>

<p align="center">
<a href="https://packagist.org/packages/laracraft-tech/laravel-xhprof"><img src="https://img.shields.io/packagist/dt/laracraft-tech/laravel-xhprof" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laracraft-tech/laravel-xhprof"><img src="https://img.shields.io/packagist/v/laracraft-tech/laravel-xhprof" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laracraft-tech/laravel-xhprof"><img src="https://img.shields.io/packagist/l/laracraft-tech/laravel-xhprof" alt="License"></a>
<a href="https://packagist.org/packages/laracraft-tech/laravel-xhprof"><img alt="Laravel Compatibility" src="https://badge.laravel.cloud/badge/laracraft-tech/laravel-xhprof"></a>
</p>

<!--
#Laravel XHProf

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
-->

## Introduction

Laravel XHProf gives you a simple way to profile your Laravel application
using the well-known XHProf PHP extension, originally developed by Facebook.
It also walks you through setting up the XHProf UI, which lets you visualize, save, and analyze
your profiling results.

<p align="center">
<img src="https://i.imgur.com/tNBhiPg.png">
</p>

## Installation

First, you'll need to install the PHP extension.
We highly recommend using Ondrej's PPA, since it's well maintained and covers pretty much every PHP version.

### Native PHP environment

If you're running PHP natively on your system, just install the XHProf extension:

``` bash
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php php-xhprof Graphviz
# Verify the extension is loaded
php -i | grep xhprof
# you might need to restart your webserver or php-fpm afterwards
```

Note: Graphviz is required to generate callgraphs.

### Laravel Sail environment

If you're using Laravel Sail, here's how to set things up:

``` bash
sail up -d
sail artisan sail:publish
# Check docker-compose.yml to see which PHP version is used under build->context (e.g. ./docker/8.1)
# Then open the matching Dockerfile:
nano docker/<php-version>/Dockerfile
# Find the block where all PHP extensions get installed and add "php<php-version>-xhprof graphviz \"
# Now rebuild Sail
sail down ; sail build --no-cache ; sail up -d # this may take a while...
# Verify the extension is loaded
sail php -i | grep xhprof
```

Note: The provided Laravel Sail Dockerfile already uses Ondrej's PPA.

### Install the Package

``` bash
composer require laracraft-tech/laravel-xhprof --dev
php artisan vendor:publish --provider="LaracraftTech\LaravelXhprof\XHProfServiceProvider" --tag="config"
```

### Install the UI

We use the fork by "preinheimer", which is the one recommended in the official PHP docs:
https://www.php.net/manual/en/xhprof.requirements.php

``` bash
mkdir public/vendor ; git clone git@github.com:preinheimer/xhprof.git ./public/vendor/xhprof
# If you haven't already, we recommend adding public/vendor to your .gitignore
echo "/public/vendor" >> .gitignore
```

### Database

The UI package stores and reads its profiling data from a table that is hard coded to ``details``.
If you already have a table with that name in your database, you'll need to take a few extra steps.
Otherwise, here's the straightforward path:

<br/>

#### If you don't already have a ``details`` table in your database:

``` bash
php artisan vendor:publish --provider="LaracraftTech\LaravelXhprof\XHProfServiceProvider" --tag="migrations"
php artisan migrate
```
<br/>

#### If you already have a ``details`` table in your database:

In this case we recommend using a separate database just for XHProf. 

``` mysql
CREATE DATABASE xhprof;
USE xhprof;
CREATE TABLE IF NOT EXISTS `details` (
  `id` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `c_url` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `server name` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perfdata` longblob,
  `type` tinyint DEFAULT NULL,
  `cookie` longblob,
  `post` longblob,
  `get` blob,
  `pmu` int DEFAULT NULL,
  `wt` int DEFAULT NULL,
  `cpu` int DEFAULT NULL,
  `server_id` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aggregateCalls_include` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `details_url_index` (`url`),
  KEY `details_c_url_index` (`c_url`),
  KEY `details_cpu_index` (`cpu`),
  KEY `details_wt_index` (`wt`),
  KEY `details_pmu_index` (`pmu`),
  KEY `details_timestamp_index` (`timestamp`),
  KEY `details_server name_timestamp_index` (`server name`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Note: don't forget to create a user that has privileges on the new database!

### Config

Now let's configure a few settings:

``` bash
cp public/vendor/xhprof/xhprof_lib/config.sample.php public/vendor/xhprof/xhprof_lib/config.php
# 1. Adjust the DB credentials to match your setup
# 2. Enable the dot_binary section
# 3. If you're working locally, set $controlIPs to false
nano public/vendor/xhprof/xhprof_lib/config.php
```

## Usage

Just set ``XHPROF_ENABLED=true`` in your .env file,
and from now on every request to your application will be profiled. \
Visit ``<your-host>/vendor/xhprof/xhprof_html/`` to view the results.

Happy analyzing!


<!-- 

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
composer test
```

 -->

## Security

If you discover any security-related issues, please email zacharias.creutznacher@gmail.com instead of using the issue tracker.

## Credits

- [Zacharias Creutznacher][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/laracraft-tech/laravel-xhprof.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/laracraft-tech/laravel-xhprof.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/laracraft-tech/laravel-xhprof/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/laracraft-tech/laravel-xhprof
[link-downloads]: https://packagist.org/packages/laracraft-tech/laravel-xhprof
[link-travis]: https://travis-ci.org/laracraft-tech/laravel-xhprof
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/laracraft-tech
[link-contributors]: ../../contributors
