vinephp
======

*PHP wrapper for the [Vine](https://vine.co) API*

## Installation

From composer

```
php composer.phar require mcarpenter/vinephp
```

From source

```
git clone https://github.com/ahsile/vinephp.git
```

## Examples

```php
use mcarpenter/vinephp

$vine = new API("username", "password");
$user = $vine->user;
$followers = $user->followers();
$timeline = $user->timeline();
```

## Acknowledgements

* Inspired on [vinepy](https://github.com/davoclavo/vinepy)
* Based on the Vine API documentation by [neuegram](https://github.com/neuegram) and [starlock](https://github.com/starlock/vino/wiki/API-Reference)
* Thanks to [Vine](https://vine.co) for making such an amazing app.
