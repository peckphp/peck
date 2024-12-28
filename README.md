
<p align="center">
    <img src="https://raw.githubusercontent.com/peckphp/peck/main/docs/logo.png" alt="Peck example" height="300">
    <p align="center">
        <a href="https://github.com/peckphp/peck/actions"><img alt="GitHub Workflow Status (master)" src="https://img.shields.io/github/actions/workflow/status/peckphp/peck/tests.yml"></a>
        <a href="https://packagist.org/packages/peckphp/peck"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/peckphp/peck"></a>
        <a href="https://packagist.org/packages/peckphp/peck"><img alt="Latest Version" src="https://img.shields.io/packagist/v/peckphp/peck"></a>
        <a href="https://packagist.org/packages/peckphp/peck"><img alt="License" src="https://img.shields.io/packagist/l/peckphp/peck"></a>
    </p>
</p>

------
**Peck** is a powerful CLI tool designed to identify wording or spelling mistakes in your codebase. Built for speed, simplicity, and seamless integration, Peck fits naturally into your workflow, much like tools such as Pint or Pest.

Leveraging the robust capabilities of **[GNU Aspell](https://en.wikipedia.org/wiki/GNU_Aspell)** via the [github.com/tigitz/php-spellchecker](https://github.com/tigitz/php-spellchecker) PHP wrapper, Peck inspects every corner of your codebase — including folder names, file names, method names, comments, and beyond — ensuring your work maintains a high standard of clarity and professionalism.

> Note: Peck is still under active development and is not yet ready for production use. Currently, only the filesystem checker is implemented, focusing exclusively on detecting spelling mistakes in file and folder names.

## Installation

> **Requires [PHP 8.3+](https://php.net/releases/)**

You can require Peck using [Composer](https://getcomposer.org) with the following command:

```bash
composer require peckphp/peck
```

## Usage

To check your project for spelling mistakes, run:

```bash
./vendor/bin/peck
```

---

Peck is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
