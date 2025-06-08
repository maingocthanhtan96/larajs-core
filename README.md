# LaraJS Core

[![Latest Version](https://img.shields.io/packagist/v/larajs/core.svg?style=flat-square)](https://packagist.org/packages/larajs/core)
[![PHP Version](https://img.shields.io/packagist/php/larajs/core.svg?style=flat-square)](https://packagist.org/packages/larajs/core)
[![Total Downloads](https://img.shields.io/packagist/dt/larajs/core.svg?style=flat-square)](https://packagist.org/packages/larajs/core)

LaraJS Core is a PHP package providing core functionalities for Laravel applications, designed to streamline development and integrate seamlessly with JavaScript frontends.

## Overview

This package includes:

-   Base controller (`BaseLaraJSController`)
-   Helper functions (`Helper.php`)
-   Middleware for language handling (`LangMiddleware`) and request/response logging (`LogRequestResponse`)
-   File service (`FileService`)
-   Traits for actions (`Action.php`) and user signatures (`UserSignature.php`)
-   Artisan commands for generating boilerplate code.

## Installation

You can install the package via composer:

```bash
composer require larajs/core
```

The package will automatically register its service provider.

## Usage

### Service Provider

The package's service provider `LaraJS\Core\LaraJSCoreServiceProvider` is auto-discovered and registered.

### Available Artisan Commands

This package provides several Artisan commands to help you generate common classes:

-   `php artisan larajs:generate-action {name}` - Generates a new action class.
-   `php artisan larajs:generate-controller {name}` - Generates a new controller class.
-   `php artisan larajs:generate-repository {name}` - Generates a new repository class.
-   `php artisan larajs:setup` - Sets up the necessary files and configurations for LaraJS Core.

### Controllers

Extend `LaraJS\Core\Controllers\BaseLaraJSController` for your controllers to leverage base functionalities.

### Helpers and Services

Utilize helpers and services provided in the `LaraJS\Core\Helpers` and `LaraJS\Core\Services` namespaces.

## Contributing

Please see CONTRIBUTING for details. (You might want to create a `CONTRIBUTING.md` file)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
