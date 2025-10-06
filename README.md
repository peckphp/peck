<p align="center">
    <a href="https://youtu.be/C1-2I_Y8ih8" target="_blank">
        <img src="/art/video.png" alt="Overview Peck PHP" style="width:70%;">
    </a>
</p>
    
<p align="center">
    <p align="center">
        <a href="https://github.com/peckphp/peck/actions"><img alt="GitHub Workflow Status (master)" src="https://img.shields.io/github/actions/workflow/status/peckphp/peck/tests.yml"></a>
        <a href="https://packagist.org/packages/peckphp/peck"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/peckphp/peck"></a>
        <a href="https://packagist.org/packages/peckphp/peck"><img alt="Latest Version" src="https://img.shields.io/packagist/v/peckphp/peck"></a>
        <a href="https://packagist.org/packages/peckphp/peck"><img alt="License" src="https://img.shields.io/github/license/peckphp/peck"></a>
    </p>
</p>

------

**Peck** is a powerful CLI tool designed to identify wording or spelling mistakes in your codebase: filenames, class names, method names, property names, docs, and more. Built for speed, simplicity, and seamless integration, Peck fits naturally into your workflow, much like tools such as Pint or Pest.

Leveraging the robust capabilities of **[GNU Aspell](https://en.wikipedia.org/wiki/GNU_Aspell)**, Peck inspects every corner of your codebase — ensuring your work maintains a high standard of clarity and professionalism.

> Note: Peck is still under active development and is not yet ready for production use.

## Installation

> **Requires [PHP 8.2+](https://php.net/releases/) and [GNU Aspell](https://en.wikipedia.org/wiki/GNU_Aspell)**

Peck relies on GNU Aspell for its spell-checking functionality. Make sure GNU Aspell is installed on your system before using Peck.

### Installing GNU Aspell

- If you are using **Debian/Ubuntu**:
```bash
sudo apt-get install aspell aspell-en
```
 
- If you are using **MacOS (using Homebrew)**:
```bash
brew install aspell
```

- If you are using **Windows**:
> We recommend moving to the **Windows Subsystem for Linux (WSL)** and following the Debian/Ubuntu steps. Alternatively, if you prefer not to use WSL, you can install Aspell using **[Scoop](https://scoop.sh/)**, a package manager for Windows:
```bash
scoop install main/aspell
```

### Installing Peck

You can require Peck using [Composer](https://getcomposer.org) with the following command:

```bash
composer require peckphp/peck --dev

./vendor/bin/peck --init
```

## Usage

To check your project for spelling mistakes, run:

```bash
./vendor/bin/peck
```

On the very first run, Peck may detect a large number of spelling mistakes. You may use the `ignore-all` option to ignore all the mistakes at once:

```bash
./vendor/bin/peck --ignore-all
```

## Configuration

Peck can be configured using a `peck.json` file in the root of your project.

You can scaffold the `peck.json` file with:
```bash
./vendor/bin/peck --init
```

Here's an example configuration:

```json
{
    "preset": "laravel",
    "ignore": {
        "words": [
            "config",
            "namespace"
        ],
        "paths": [
            "app/MyFolder",
            "app/MyFile.php"
        ]
    }
}
```

You can also specify the path to the configuration file using the `--config` option:

```bash
./vendor/bin/peck --config relative/path/to/peck.json
```
### Presets

In order to make it easier to get started with Peck, we've included a few presets that you can use to ignore common words in your project. The following presets are available:

- `laravel` 

### Languages

While by default `peck` verfies the spelling by using GNU Aspell's `en_US` language dictionary, you can optionally specify a different (installed) language to be passed using the "language" key in the configuration:

```json
{
    "preset": "laravel",
    "language": "en_US",
    "ignore": {
        "words": [
            "config",
            "namespace"
        ]
    }
}
```

You can see a full list of available dictionaries using the command `aspell dump dicts`.

## Command Options

The behaviour of `peck` can be modified with the following command options:

#### `--init`

If you don't have a `peck.json` file yet, you can create a blank configuration file by using the `--init` option.

#### `--config`

By default `peck` will check for a `peck.json` file in your project root. If one isn't available it will try to figure out the directory to check by itself.

#### `--path`

The path to check can be overwritten with the `--path` option. If the path is one you always need checking you can place it in your `peck.json` file.

#### `--text`

The `--text` option allows you to check a string of text for spelling mistakes. This is useful when you want to check a specific string, such as commit messages.

#### `--ignore-all`

This option will ignore all spelling mistakes in the current run. This is useful when you have a large number of mistakes and want to ignore them all at once.

## CI / GitHub Actions

When running Peck on GitHub Actions, you can use the following workflow before running Peck:

```yaml
    - name: Install Aspell
      shell: bash
      run: |
          if [[ "$RUNNER_OS" == "Linux" ]]; then
            sudo apt-get update && sudo apt-get install -y aspell aspell-en
          elif [[ "$RUNNER_OS" == "macOS" ]]; then
            brew install aspell
          fi

    - name: Check Typos
      shell: bash
      run: |
          if [[ "$RUNNER_OS" == "Linux" || "$RUNNER_OS" == "macOS" ]]; then
            composer test:typos
          fi
```

---

Peck is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.

