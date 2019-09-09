<?php namespace lang\ast\syntax\php\unittest;

use lang\IllegalArgumentException;
use lang\Runnable;
use lang\ast\Errors;
use lang\ast\unittest\emit\EmittingTest;

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

  #[@test, @values([
  #  [true, 'bool'],
  #  [false, 'bool'],
  #  [null, 'void'],
  #])]
  public function exact_comparison($arg, $expected) {
    $this->assertEquals($expected, $this->typeFixture()->newInstance()->run($arg));
  }

  #[@test, @values([
  #  [1, 'integer'],
  #  ['Test', 'string'],
  #])]
  public function native_type_comparison($arg, $expected) {
    $this->assertEquals($expected, $this->typeFixture()->newInstance()->run($arg));
  }

  #[@test, @values([
  #  [[1], 'integers'],
  #  [['Test'], 'strings'],
  #])]
  public function array_type_comparison($arg, $expected) {
    $this->assertEquals($expected, $this->typeFixture()->newInstance()->run($arg));
  }

  #[@test]
  public function value_type_comparison() {
    $this->assertEquals('runnable-instance', $this->typeFixture()->newInstance()->run(newinstance(Runnable::class, [], [
      'run' => function() { }
    ])));
  }

  #[@test]
  public function function_type_comparison() {
    $this->assertEquals('runnable-function', $this->typeFixture()->newInstance()->run(function(Runnable $a) { }));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function unhandled() {
    $this->typeFixture()->newInstance()->run($this);
  }

  #[@test, @expect(IllegalArgumentException::class)]
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

  #[@test, @expect(Errors::class)]
  public function empty_switch_does_not_compile() {
    $this->type('class <T> {
      public function run() {
        return switch (true) { };
      }
    }');
  }

  #[@test]
  public function execute_blocks() {
    $r= $this->run('class <T> {
      public function run() {
        return switch (true) {
          case true  => { $a= 1; return ++$a; }
          case false => 1;
        };
      }
    }');
    $this->assertEquals(2, $r);
  }
}