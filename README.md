# Symfony console commands collector

## First steps

There is a code which could help you to merge sub-commands from different places.

Let's admit we have command `super` which is an enter-point for our console commands.

`super` has sub-commands `sub1` and `sub2`.

We have packages which could extend this command. There two sub-commands in two packages.

```
bin/
    .autoload-init.php
    super
src/
    SubOneCommand.php
    SubTwoCommand.php
vendor/
    my-vendor/
        pack111/
            bin/super-app-include.php
            src/FooCommand.php
        pack222/
            bin/super-app-include.php
            src/BarCommand.php
composer.json
```

Content of `bin/super`
```php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/.autoload-init.php';

use Symfony\Component\Console\Application as App;
use Rikby\SymfonyConsole\CommandCollector\Collector;

$app = new App('Super shell console.', '0.1.0');
$app->add(new SubOneCommand());
$app->add(new SubTwoCommand());

//collect other commands
$collector = new Collector();
$collector->setPaths([__DIR__.'/../vendor/*/*/bin'])
    ->setName('super')
    ->setCompiledFile(__DIR__.'/.build-super.php')
    ->captureCommands();

// here .build-super.php must be generated even it's empty
require_once __DIR__.'/.build-super.php';

$app->run();
```

Code `->setPaths([__DIR__.'/../vendor/*/*/bin'])` will push to search files in `bin/` directory within all packages.
Target filename is `super-app-inlclude.php` because of `->setName('super')` and format `%s-app-inlclude.php`.

Content of `vendor/my-vendor/pack111/bin/super-app-include.php`:
```php
<?php
/** @var $app Symfony\Component\Console\Application */
$app->add(new My\PackOne\FooCommand());
```

Content of `vendor/my-vendor/pack222/bin/super-app-include.php`:
```php
<?php
/** @var $app Symfony\Component\Console\Application */
$app->add(new My\PackTwo\BarCommand());
```

As you may suspected the generated file `bin/.build-super.php` will contain following code of these two files `super-app-include.php`.
```php
<?php
/** @var $app Symfony\Component\Console\Application */
$app->add(new My\PackOne\FooCommand());
/** @var $app Symfony\Component\Console\Application */
$app->add(new My\PackTwo\BarCommand());
```

In case you need to regenerate the file you have two options:
- Remove file `bin/.build-super.php`
- Call `CollectorTrait::forceCaptureCommands()`

## Integrate into a class
Actually there is the trait `Rikby\SymfonyConsole\CommandCollector\CollectorTrait` which can be used anywhere.<br>
Class `Rikby\SymfonyConsole\CommandCollector\Collector` just wraps it.

## TBD
There is an approach for including `***-app-include.php` instead merging them.<br>
Maybe it would be useful to links to these files.
