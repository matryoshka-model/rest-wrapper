# Contributing

Contributions to Matryoshka REST wrapper are always welcomed and encouraged.

You make our lives easier by sending us your contributions through github pull requests.

* Coding standard for the project is [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)

* Any contribution must provide tests for additional introduced conditions

## Team members

The core team members are:

| Name            | Nickname                             |
|:---------------:|:------------------------------------:|
| Leonardo Grasso | [leogr](http://github.com/leogr)     |
| Leo Di Donato   | [leodido](http://github.com/leodido) |
| Antonio Visalli | [visa4](http://github.com/visa4)     |

## Got a question or problem?

If you have questions about how to use REST wrapper please write us at <ripaclub@gmail.com>.

Other communication channels will be activated soon. In the mean time you can also contact us writing a [new issue](https://github.com/matryoshka-model/rest-wrapper/issues/new).

Due to time constraints, we are not always able to respond as quickly as we would like. Please do not take delays personal and feel free to remind us.

## New features

You can request a new feature by submitting an issue to our github repository. If you would like to implement a new feature then consider what kind of change it is:

* **Major changes**

    This kind of contribution should be discussed first with us in issues. This way we can better coordinate our efforts, prevent duplication of work, and help you to craft the change so that it is successfully accepted into the project.

* **Small changes**

    Can be crafted and submitted to the github repository as a pull request.

## Bug triage

Bug triaging is managed via github [issues](https://github.com/matryoshka-model/rest-wrapper/issues).

You can help report bugs by filing them [here](https://github.com/matryoshka-model/rest-wrapper/issues).

Before submitting new bugs please verify that similar ones do not exists yet. This will help us to reduce the duplicates and the references between issues.

Is desiderable that you provide reproducible behaviours attaching (failing) tests.

## Testing

The PHPUnit version to be used is the one installed as a dev-dependency via [composer](https://getcomposer.org/):

```bash
$ ./vendor/bin/phpunit
```

## Versioning

- **Master branch**

    Last stable release

- **Develop branch**

    Next minor release

See `extras` field in the `composer.json` file for further details, i.e. aliases.

## Workflow

Matryoshka REST wrapper is versioned following matryoshka library versions.

Suppose you want to release a new version.

Scenario: the last stable version of REST wrapper is `0.4.0`, then its `develop` branch corresponds to version `0.5.x-dev`.

1. Commit and push on `develop` branch

   Remember to update branch alias and matryoshka dependency in the `composer.json`. E.g.,

   ```json
   {
     ...
     "require": {
       "php": ">=5.4",
       "matryoshka-model/matryoshka": "~0.5.0"
     },
     "extra": {
       "branch-alias": {
         "dev-master": "0.5.x-dev",
         "dev-develop": "0.6.x-dev"
       }
     }
     ...
   }
   ```

2. Pull request against the `master` branch

3. Tag release `v0.5.0`

4. Checkout again the `develop` branch

5. Setup [matryoshka](https://github.com/matryoshka-model/matryoshka) dependency in `composer.json` to permit the development of the following version

    ```json
    {
         ...
         "require": {
           "php": ">=5.4",
           "matryoshka-model/matryoshka": "0.6.x-dev"
         }
         ...
     }
    ```

    This way executing a `composer update -o` in your local REST wrapper repository your vendor will contain the `develop` of matryoshka library.

## Contributing process

What branch to issue the pull request against?

For **new features**, or fixes that introduce **new elements to the public API** (such as new public methods or properties), issue the pull request against the `develop` branch.

For **hotfixes** against the stable release, issue the pull request against the `master` branch.

1. **Fork** the rest-wrapper [repository](https://github.com/matryoshka-model/rest-wrapper/fork)

2. **Checkout** the forked repository

3. Retrieve **dependencies** using [composer](https://getcomposer.org/)

4. Create your **local branch**, **commit** your code and **push** your local branch to your github fork

5. Send us a **pull request** as descripted for your changes to be included

Please remember that **any contribution must provide tests** for additional introduced conditions. Accepted coverage for new contributions is 75%. Any contribution not satisfying this requirement won't be merged.

Don't get discouraged!
