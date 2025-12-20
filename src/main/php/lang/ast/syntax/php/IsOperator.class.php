<?php namespace lang\ast\syntax\php;

use lang\ast\Node;
use lang\ast\nodes\{Assignment, BinaryExpression, Braced, InstanceOfExpression, InvokeExpression, Literal, Variable};
use lang\ast\syntax\Extension;
use lang\ast\types\{IsArray, IsFunction, IsMap, IsUnion, IsIntersection};

class IsOperator implements Extension {

  public function setup($language, $emitter) {
    $language->infix('is', 60, function($parse, $token, $left) {
      $t= $this->type($parse, true);

      $node= new InstanceOfExpression($left, $t ?: $this->expression($parse, 0));
      $node->kind= 'is';
      return $node;
    });

    $test= function($literal, $expr, $temp) {
      static $is= [
        'string'   => true,
        'int'      => true,
        'float'    => true,
        'bool'     => true,
        'array'    => true,
        'object'   => true,
        'callable' => true,
        'iterable' => true,
      ];

      if (isset($is[$literal])) {
        return new InvokeExpression(new Literal('is_'.$literal), [$expr]);
      } else if ('mixed' === $literal) {
        return new Literal('true');
      } else {
        return new InstanceOfExpression($expr, $literal);
      }
    };

    $emitter->transform('is', function($codegen, $node) use($test) {
      $t= $node->type;
      if ($t instanceof Node) {
        return new InvokeExpression(new Literal('is'), [$node->type, $node->expression]);
      }

      // Verify builtin primitives with is_XXX(), value types with instanceof, others using is()
      if ($t instanceof IsFunction || $t instanceof IsArray || $t instanceof IsMap || $t instanceof IsUnion || $t instanceof IsIntersection) {
        return new InvokeExpression(new Literal('is'), [new Literal('"'.$t->name().'"'), $node->expression]);
      } else {
        $literal= $t->literal();
        $temp= new Variable($codegen->symbol());
        if ('?' === $literal[0]) {
          return new BinaryExpression(
            new BinaryExpression(new Literal('null'), '===', new Braced(new Assignment($temp, '=', $node->expression))),
            '||',
            $test(substr($literal, 1), $temp, null)
          );
        } else {
          return $test($literal, $node->expression, $temp);
        }
      }
    });
  }
}