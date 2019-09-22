<?php namespace lang\ast\syntax\php;

use lang\ast\Node;
use lang\ast\nodes\Braced;
use lang\ast\nodes\InvokeExpression;
use lang\ast\nodes\LambdaExpression;
use lang\ast\nodes\Signature;

class CaseBlock extends Node {
  public $kind= 'caseblock';
  public $conditions, $statements;

  public function __construct($conditions, $statements) {
    $this->conditions= $conditions;
    $this->statements= $statements;
  }

  /**
   * Transforms case body into an expression by turning statement lists into an IIFE.
   *
   * @return lang.ast.Node
   */
  public function expression() {
    return new InvokeExpression(new Braced(new LambdaExpression(new Signature([], null), $this->statements)), []);
  }

  /** @return iterable */
  public function children() {
    foreach ((array)$this->conditions as $node) {
      yield $node;
    }
    foreach ($this->statements as $node) {
      yield $node;
    }
  }
}