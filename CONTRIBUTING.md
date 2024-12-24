# CONTRIBUTING

Contributions are welcome, and are accepted via pull requests.
Please review these guidelines before submitting any pull requests.

## Process

1. Fork the project
1. Create a new branch
1. Code, test, commit and push
1. Open a pull request detailing your changes. Make sure to follow the [template](.github/PULL_REQUEST_TEMPLATE.md)

## Guidelines

* Please ensure the coding style running `composer lint`.
* Send a coherent commit history, making sure each individual commit in your pull request is meaningful.
* You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
* Please remember that we follow [SemVer](http://semver.org/).

## Setup

Clone your fork, then install the dev dependencies:
```bash
composer install
```
## Lint

Lint your code:
```bash
composer lint
```
## Tests

Run all tests:
```bash
composer test
```

Check types:
```bash
composer test:types
```

Unit tests:
```bash
composer test:unit
```

## 🐳 Docker setup
If you have **Docker** installed, you can quickly set up your environment for Pest using our Docker configuration. Follow these steps:

1.	**Build the Docker Image:**
```bash
docker compose build
```

2.	**Install Dependencies:**
```bash
docker compose run --rm composer install
```

3.	**Run Tests and Analysis Tools:**
```bash
docker compose run --rm composer test
```

4. **Run Peck:**
```bash
docker compose run --rm php ./bin/peck
```

### 🔄 Specify PHP Version (Optional)
If you want to check things work against a **specific version of PHP**, you may include the `PHP` build argument when building the image:
```bash
docker compose build --build-arg PHP=8.4
```
**Note:** By default, the lowest PHP version supported by Pest will be used.
