<?php namespace lang\ast\syntax\php;

use lang\ast\ArrayType;
use lang\ast\FunctionType;
use lang\ast\MapType;
use lang\ast\Node;
use lang\ast\Type;
use lang\ast\nodes\ArrayLiteral;
use lang\ast\nodes\Assignment;
use lang\ast\nodes\BinaryExpression;
use lang\ast\nodes\Braced;
use lang\ast\nodes\CaseLabel;
use lang\ast\nodes\InstanceOfExpression;
use lang\ast\nodes\InvokeExpression;
use lang\ast\nodes\LambdaExpression;
use lang\ast\nodes\Literal;
use lang\ast\nodes\NewExpression;
use lang\ast\nodes\OffsetExpression;
use lang\ast\nodes\Signature;
use lang\ast\nodes\SwitchStatement;
use lang\ast\nodes\TernaryExpression;
use lang\ast\nodes\ThrowExpression;
use lang\ast\nodes\Variable;
use lang\ast\syntax\Extension;

class SwitchExpression implements Extension {

  public function setup($language, $emitter) {
    $language->prefix('switch', 0, function($parse, $token) {
      $parse->expecting('(', 'switch');
      $condition= $this->expression($parse, 0);
      $parse->expecting(')', 'switch');

      $cases= [];
      $parse->expecting('{', 'switch');
      while ('}' !== $parse->token->value) {
        if ('case' === $parse->token->value) {
          $expr= [];
          do {
            $parse->forward();
            if ('is' === $parse->token->value) {
              $parse->forward();
              $expr[]= $this->type($parse, false);
            } else {
              $expr[]= $this->expression($parse, 0);
            }
          } while (',' === $parse->token->value);
        } else if ('default' === $parse->token->value) {
          $parse->forward();
          $expr= null;
        } else {
          $parse->expecting('Either default or case', 'switch');
          break;
        }

        // => expr vs. => { stmt; stmt; ... }
        $parse->expecting('=>', 'switch');
        if ('{' === $parse->token->value) {
          $parse->forward();
          $cases[]= new CaseBlock($expr, $this->statements($parse));
          $parse->expecting('}', 'switch');
        } else {
          $cases[]= new CaseExpression($expr, $this->expression($parse, 0));
          $parse->expecting(';', 'switch');
        }
      }
      $parse->forward();

      if (empty($cases)) {
        $parse->expecting('One ore more cases', 'switch');
      }

      $stmt= new SwitchStatement($condition, $cases);
      $stmt->kind= 'switchexpr';
      return $stmt;
    });

    $emitter->transform('switchexpr', function($codegen, $node) {
      static $is= [
        'string'   => true,
        'int'      => true,
        'float'    => true,
        'bool'     => true,
        'array'    => true,
        'object'   => true,
        'iterable' => true,
        'callable' => true
      ];

      // Generate cascade of braced ternary expressions
      $t= new Variable($codegen->symbol());
      $ternary= new TernaryExpression(null, null, null);
      $ptr= &$ternary;
      foreach ($node->cases as $case) {
        if (null === $case->conditions) {
          $ptr->otherwise= $case->expression();
        } else foreach ($case->conditions as $i => $expr) {
          if ($expr instanceof FunctionType || $expr instanceof ArrayType || $expr instanceof MapType) {
            $cond= new InvokeExpression(new Literal('is'), [new Literal('"'.$expr->name().'"'), $t]);
          } else if ($expr instanceof Type) {
            $type= $expr->literal();
            if (isset($is[$type])) {
              $cond= new InvokeExpression(new Literal('is_'.$type), [$t]);
            } else {
              $cond= new InstanceOfExpression($t, new Literal($type));
            }
          } else {
            $cond= new BinaryExpression($t, '===', $expr);
          }

          $ptr->otherwise= new Braced(new TernaryExpression($cond, $case->expression(), null));
          $ptr= &$ptr->otherwise->expression;
        }
      }

      // If no default case was supplied, generate one which raises an exception
      if (null === $ptr->otherwise) {
        $ptr->otherwise= new ThrowExpression(new NewExpression(
          '\lang\IllegalArgumentException',
          [new Literal('"Unhandled default case"')]
        ));
      }

      return new OffsetExpression(
        new ArrayLiteral([[null, new Assignment($t, '=', $node->expression)], [null, $ternary->otherwise]]),
        new Literal(1)
      );
    });
  }
}