<?php namespace lang\ast\syntax\php;

use lang\ast\ArrayType;
use lang\ast\FunctionType;
use lang\ast\MapType;
use lang\ast\Node;
use lang\ast\UnionType;
use lang\ast\nodes\Assignment;
use lang\ast\nodes\BinaryExpression;
use lang\ast\nodes\Braced;
use lang\ast\nodes\InstanceOfExpression;
use lang\ast\nodes\InvokeExpression;
use lang\ast\nodes\Literal;
use lang\ast\nodes\Variable;
use lang\ast\syntax\Extension;

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
        'callable' => true
      ];

      // PHP 7.0 compatibility, is_iterable() doesn't exist there
      if ('iterable' === $literal) {
        return new BinaryExpression(
          new InstanceOfExpression($temp ? new Braced(new Assignment($temp, '=', $expr)) : $expr, '\Traversable'),
          '||',
          new InvokeExpression(new Literal('is_array'), [$temp])
        );
      } else if (isset($is[$literal])) {
        return new InvokeExpression(new Literal('is_'.$literal), [$expr]);
      } else {
        return new InstanceOfExpression($expr, new Literal($literal));
      }
    };

    $emitter->transform('is', function($codegen, $node) use($test) {
      $t= $node->type;
      if ($t instanceof Node) {
        return new InvokeExpression(new Literal('is'), [$node->type, $node->expression]);
      }

      // Verify builtin primitives with is_XXX(), value types with instanceof, others using is()
      if ($t instanceof FunctionType || $t instanceof ArrayType || $t instanceof MapType || $t instanceof UnionType) {
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