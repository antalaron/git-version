GitVersion
==========

![Tests](https://github.com/antalaron/git-version/actions/workflows/push.yaml/badge.svg?branch=master)

PHP library to get the Git version of the project.

This library does not depend on the Git.

Installation
------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this library:

```bash
$ composer require antalaron/git-version
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Usage
-----

To get the version:

```php
$gitVersion = new \Antalaron\GitVersion\GitVersion();
$gitVersion->getVersion(__DIR__);

// or static
\Antalaron\GitVersion\GitVersion::getGitVersion(__DIR__);
```

If no git found, then the return value is `null`.

There is a second `$hashLenght` parameter in the methods. With that, you will get
the first n character of the hash.

To get the latest commit message:

```php
$gitVersion = new \Antalaron\GitVersion\GitVersion();
$gitVersion->getLatestCommit(__DIR__);

// or static
\Antalaron\GitVersion\GitVersion::getGitLatestCommit(__DIR__);
```

On error, the return value is `null`.

To get the latest commit object:

```php
$gitVersion = new \Antalaron\GitVersion\GitVersion();
$gitVersion->getLatestCommitDetails(__DIR__);

// or static
\Antalaron\GitVersion\GitVersion::getGitLatestCommitDetails(__DIR__);
```

License
-------

This project is under [MIT License](http://opensource.org/licenses/mit-license.php).
