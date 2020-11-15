Is operator for PHP
===================

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-lang/php-is-operator.svg)](http://travis-ci.org/xp-lang/php-is-operator)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-lang/php-is-operator/version.png)](https://packagist.org/packages/xp-lang/php-is-operator)

Plugin for the [XP Compiler](https://github.com/xp-framework/compiler/) which adds an `is` operator to the PHP language.

Before
------
A mix of functions, syntax and XP core functionality `is()`:

```php
is_string($value)                                  // for primitives, use is_[T]()
is_callable($value)                                // for pseudo types callable, array, object
is_array($value) || $value instanceof \Traversable // no is_iterable in PHP 5 and 7.0 
$value instanceof Date                             // for value types
null === $value || is_int($value)                  // nullable types cannot be tested directly
is('[:string]', $value)                            // for types beyond PHP type system
is('string|util.URI', $value)                      // for types beyond PHP type system
```

After
-----
Anything that works as a parameter, property or return type can be used with the `is` operator.

```php
$value is string
$value is callable
$value is iterable
$value is Date
$value is ?int
$value is array<string, string>
$value is string|URI
```

Installation
------------
After installing the XP Compiler into your project, also include this plugin.

```bash
$ composer require xp-framework/compiler
# ...

$ composer require xp-lang/php-is-operator
# ...
```

No further action is required.

See also
--------
* https://wiki.php.net/rfc/pattern-matching
* https://docs.hhvm.com/hack/expressions-and-operators/is
* https://kotlinlang.org/docs/reference/typecasts.html
* https://docs.microsoft.com/en-us/dotnet/csharp/language-reference/keywords/is
