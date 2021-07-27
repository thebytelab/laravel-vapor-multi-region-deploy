# Laravel Vapor: Multi-Region Deploy

---
Provides an artisan command to assist deploying your Laravel Vapor app to multiple AWS regions.

## Installation

```shell
composer require thebytelab/vapor-multi-region-deploy 
```

## Setup & Usage

The `vapor:multi-region:deploy` command assumes you have a directory in your project root called `vapor` which contains
all of the vapor.yml manifests following a naming pattern similar to the examples below:
- us-east.vapor.yml
- test.vapor.yml
- node1.vapor.yml

The steps to getting started:
1. If you haven't got one already, create a `vapor` directory in your project root.
2. Move any vapor files to this new folder and rename them to something that makes sense following the pattern above.
3. Run `php artisan vapor:multi-region:deploy` to deploy your app to multiple Vapor projects or regions.

## Advanced usage

The following options can be used to modify default behaviour, some options are inherited from the `vapor deploy`
command:

### `--bin` (string)

Relative location of the laravel/vapor-cli executable. Defaults to `vendor/bin/vapor` in the project root.

Example usage: `php artisan vapor:multi-region:deploy --bin=/usr/local/bin/vapor`

### `--vapors` (string)

Relative location to the folder containing the *.vapor.yml files to use. Defaults to looking for the `vapor` folder in
the project root.

Example usage: `php artisan vapor:multi-region:deploy --vapors=/build/vapor`

### `--commit` (string)

The commit hash that is being deployed.

Example usage: `php artisan vapor:multi-region:deploy --commit=57566c1419cdacf00ff00f781b62fac670d7aee3`

### `--message` (string)

The message for the commit that is being deployed.

Example usage: `php artsian vapor:multi-region:deploy --message="Added a new feature"`

### `--without-waiting`

Deploy without waiting for progress. May help speed up deployments to multiple Vapor projects or regions.

Example usage: `php artisan vapor:multi-region:deploy --without-waiting`

### `--fresh-assets`

Upload a fresh copy of all assets.

Example usage: `php artisan vapor:multi-region:deploy --fresh-assets`