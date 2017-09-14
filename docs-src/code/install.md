# Installation

The simplest way to install is to use [composer](https://getcomposer.org/download/):

```
php composer.phar require nathanjosiah/zf2-autotable:dev-master
```

Then, enable the module in your application config (typically `config/app.config.php`) under `modules`. For example:

```php
<?php
$config =  [
  'modules' => [
    'AutoTable', // Added here
    'Application',
    'OtherModules',
  ],
  // ...
];
```

Now you can start configuring your `ServiceManager` as demonstrated in the rest of the documentation.

