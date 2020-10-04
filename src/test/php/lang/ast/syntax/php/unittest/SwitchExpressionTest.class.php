<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\Errors;
use lang\ast\unittest\emit\EmittingTest;
use lang\{IllegalArgumentException, Runnable};
use unittest\{Assert, Expect, Test, Values};

class SwitchExpressionTest extends EmittingTest {

  /** @return lang.XPClass */
  private function typeFixture() {
    return $this->type('use lang\{Runnable, IllegalArgumentException}; class <T> {
      public function run($arg) {
        return switch ($arg) {
          case true, false                => "bool";
          case null                       => "void";
          case is int                     => "integer";
          case is string                  => "string";
          case is array<int>              => "integers";
          case is array<string>           => "strings";
          case is Runnable                => "runnable-instance";
          case is function(Runnable): var => "runnable-function";
          default                         => throw new IllegalArgumentException("Unhandled ".typeof($arg));
        };
      }
    }');
  }

  #[Test, Values([[true, 'bool'], [false, 'bool'], [null, 'void'],])]
  public function exact_comparison($arg, $expected) {
    Assert::equals($expected, $this->typeFixture()->newInstance()->run($arg));
  }

  #[Test, Values([[1, 'integer'], ['Test', 'string'],])]
  public function native_type_comparison($arg, $expected) {
    Assert::equals($expected, $this->typeFixture()->newInstance()->run($arg));
  }

  #[Test, Values([[[1], 'integers'], [['Test'], 'strings'],])]
  public function array_type_comparison($arg, $expected) {
    Assert::equals($expected, $this->typeFixture()->newInstance()->run($arg));
  }

  #[Test]
  public function value_type_comparison() {
    Assert::equals('runnable-instance', $this->typeFixture()->newInstance()->run(newinstance(Runnable::class, [], [
      'run' => function() { }
    ])));
  }

  #[Test]
  public function function_type_comparison() {
    Assert::equals('runnable-function', $this->typeFixture()->newInstance()->run(function(Runnable $a) { }));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function unhandled() {
    $this->typeFixture()->newInstance()->run($this);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function without_default_case() {
    $this->run('class <T> {
      public function run() {
        return switch (true) {
          case false => "Cannot be reached!";
          // default omitted
        };
      }
    }');
  }

  #[Test, Expect(Errors::class)]
  public function empty_switch_does_not_compile() {
    $this->type('class <T> {
      public function run() {
        return switch (true) { };
      }
    }');
  }

  #[Test]
  public function execute_blocks() {
    $r= $this->run('class <T> {
      public function run() {
        return switch (true) {
          case true  => { $a= 1; return ++$a; }
          case false => 1;
        };
      }
    }');
    Assert::equals(2, $r);
  }
}