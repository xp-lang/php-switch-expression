<?php namespace lang\ast\syntax\php;

use lang\ast\Node;

class CaseExpression extends Node {
  public $kind= 'caseexpr';
  public $conditions, $expression;

  public function __construct($conditions, $expression) {
    $this->conditions= $conditions;
    $this->expression= $expression;
  }

  /** @return lang.ast.Node */
  public function expression() {
    return $this->expression;
  }

  /** @return iterable */
  public function children() {
    foreach ((array)$this->conditions as $node) {
      yield $node;
    }
    if (null !== $this->expression) {
      yield $this->expression;
    }
  }
}