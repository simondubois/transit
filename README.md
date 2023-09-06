
<p align="center">
    <img src="https://raw.githubusercontent.com/simondubois/transit/master/screenshot.jpg">
</p>

<p align="center">
    Route planner in Sweden for aggregated calendars.<br>
</p>

## Status

This application is under heavy development.

## Features

- @todo.

## Requirements

- a web server (see [Laravel Documentation](https://laravel.com/docs/10.x/deployment#server-requirements)).
- PHP >= 8.2.
- MariaDB 10.10+ or MySQL 5.7+.
- [composer](https://getcomposer.org/).

## Deployment

1. Download the code to an empty folder:
```bash
git clone git@github.com:simondubois/transit.git .
```
2. Install the dependencies:
```bash
composer install --optimize-autoloader --no-dev
```
3. Create the configuration file:
```bash
cp .env.example .env
```
4. Generate the application key:
```bash
php artisan key:generate
```
5. Point the web server to the `public` folder.
