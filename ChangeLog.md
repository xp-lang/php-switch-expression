Switch expression for PHP - ChangeLog
=====================================

## ?.?.? / ????-??-??

## 1.0.1 / 2020-04-04

* Fixed emitting errors for `instanceof` - @thekid

## 1.0.0 / 2019-11-30

* Implemented xp-framework/rfc#334: Drop PHP 5.6. The minimum required
  PHP version is now 7.0.0!
  (@thekid)

## 0.3.0 / 2019-09-22

* Refactored code to use specialized `lang.ast.Node` subclasses instead of
  misusing the `CaseLabel` class from the `lang.ast` package.
  (@thekid)

## 0.2.0 / 2019-09-09

* Updated dependency to newest version of `xp-framework/compiler`, see
  https://github.com/xp-framework/compiler/releases/tag/v4.0.0
  (@thekid)
* Added support for PHP 5.6 - @thekid

## 0.1.0 / 2019-09-09

* Hello World! First release - @thekid