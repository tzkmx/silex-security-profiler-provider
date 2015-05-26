# Security profiler for Silex apps

For some reason the security profiler component is absent from the standard Silex web profiler. This provider enables it.

## Installation

Add the composer require:

```json
{
    "require": {
        "kurl/silex-security-profiler-provider": "*"
    }
}

```

And then run:

```bash
$ composer update
```

## Usage

This provider inherits the same configuration as the standard web profiler. Quite a few default providers are required
to get the provider up and running...

```php
<?php

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(
    new Silex\Provider\ServiceControllerServiceProvider(),
    array(
        // Inject your security configuration here 
        'security.firewalls'    => array()
    )
);

$app->register(
    new Kurl\Silex\Provider\WebProfilerServiceProvider(),
    array(
        'profiler.cache_dir' => '/path/to/cache/'
    )
);

```

## Notes

The coverage tests take forever to run for a reason which evades me. The straight up tests are fine so I hope it is not 
causing an issue. 

### That was it!