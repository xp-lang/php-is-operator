Is operator for PHP
===================

[![Build status on GitHub](https://github.com/xp-lang/php-is-operator/workflows/Tests/badge.svg)](https://github.com/xp-lang/php-is-operator/actions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_4plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-lang/php-is-operator/version.svg)](https://packagist.org/packages/xp-lang/php-is-operator)

Plugin for the [XP Compiler](https://github.com/xp-framework/compiler/) which adds an `is` operator to the PHP language compatible with the [PHP pattern matching RFC](https://wiki.php.net/rfc/pattern-matching).

Before
------
A mix of operators, functions, syntax and XP core functionality `is()`:

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

Literal patterns
----------------
Tests using the identity comparison:

```php
$value is 'test';
$value is 5;
$value is 3|5|null;
$value is 'heart'|'spade';
$value is self::Wild;
$value is 'heart'|'spade'|self::Wild;
```

Numeric comparison patterns
---------------------------
With greater than (or equal) as well as less than (or equal) operators.

```php
$value is <10;
$value is >=5;
$value is >5 & <10;
```

Structural patterns
-------------------
Objects:

```php
$value is Point(x: 3);      // Matches any Point whose $x property is 3
$value is Point(x: 4|5);    // Matches any Point whose $x property is 4 or 5
$value is Point(y: 3)|null; // Matches any Point whose $y property is 3, allowing `null`
$value is Point(y: >0);     // Matches any Point whose $x property is greater than 0

```

Arrays:

```php
$value is [1, 2, 3, 4];     // Exact match
$value is [1, 2, 3, ...];   // Begins with 1, 2, 3, but may have other entries
$value is [1, 2, mixed, 4]; // Allows any value in the 3rd position
$value is [1, 2, 3|4, 5];   // 3rd value may be 3 or 4
```

Maps:

```php
$value is ['a' => 'A', 'b' => 'B']; // Exact key/value match, but order doesn't matter
$value is ['b' => 'B', ...];        // Must have a 'b' key with value 'B', and more
$value is ['b' => mixed, ...];      // Must have a 'b' key with any value, and more
```

Capturing values
----------------
Binding variables to object properties as well as array and map values:

```php
$value is Point(x: 3, y: $y);           // If $p->x === 3, bind $p->y to $y
$value is ['a' => 'A', 'b' => $b];      // Bind value of key 'b' to $b
$value is ['op' => 'drop', ... $items]; // Bind rest of array to $items
```

Match statement
---------------
The operator can be used in the cases of a *match* statement:

```php
$result= match ($value) {
  is int    => $value,
  is string => '"'.strtr($value, ['"' => '\\"']).'"',
  null      => 'null',
};
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
* https://externals.io/message/120655 (Proposal to introduce is operator)