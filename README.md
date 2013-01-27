VersionEye Module
=================

[![Build Status](https://travis-ci.org/Ocramius/VersionEyeModule.png)](https://travis-ci.org/Ocramius/VersionEyeModule)

This library aims at providing a simple integration layer with the [VersionEye API](http://www.versioneye.com/api).

Installation
------------

Suggested installation method is through [composer](http://getcomposer.org/):

```php
php composer.phar require ocramius/version-eye-module:1.0.*
```

Setup
-----

If you use Zend Framework 2, you can now enable this module in your application by
adding it to `config/application.config.php` as `VersionEyeModule`.

After enabling it, you should visit the [VersionEye website](https://www.versioneye.com/settings/api) and
get your API key.

You can then drop a new file called `versioneye.local.php` in your `config/autoload/` directory, and put
following in it:

```php
<?php
return array(
    'version_eye_module' => array(
        'api_key' => '6e74f3f0758063c88880',
    ),
);
```

That's it!

Toolbar
-------

If you use Zend Framework 2 and this module, you may want to
install [ZendDeveloperTools](https://github.com/zendframework/ZendDeveloperTools).

This will allow you to have constant overview on the update status of the packages in your application.