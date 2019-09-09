Switch statement for PHP
========================

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-lang/php-switch-expression.svg)](http://travis-ci.org/xp-lang/php-switch-expression)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-lang/php-switch-expression/version.png)](https://packagist.org/packages/xp-lang/php-switch-expression)

Plugin for the [XP Compiler](https://github.com/xp-framework/compiler/) which adds switch expressions to the PHP language.

Example
-------
```php
use util\Date;
use lang\IllegalArgumentException;

public function serialize($arg) {
  return switch ($arg) {
    case true      => 'b:1;';
    case false     => 'b:0;';
    case null      => 'N;';
    case is int    => 'i:'.$arg.';';
    case is string => 's:'.strlen($arg).'"'.$arg.'";';
    case is Date   => '@:'.$arg->getTime().';';
    default        => throw new IllegalArgumentException('Unhandled '.typeof($arg));
  };
}
```

See also
--------
https://blog.codefx.org/java/switch-expressions/
https://kotlinlang.org/docs/reference/control-flow.html#when-expression
https://docs.microsoft.com/en-us/dotnet/csharp/language-reference/proposals/csharp-8.0/patterns#switch-expression