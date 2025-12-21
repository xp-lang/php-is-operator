<?php namespace lang\ast\syntax\php;

use lang\ast\Node;
use lang\ast\nodes\{
  Assignment,
  ArrayLiteral,
  BinaryExpression,
  Braced,
  InstanceOfExpression,
  InvokeExpression,
  Literal,
  OffsetExpression,
  InstanceExpression,
  ScopeExpression,
  Variable
};
use lang\ast\syntax\Extension;
use lang\ast\types\{IsArray, IsLiteral, IsFunction, IsMap, IsUnion, IsIntersection, IsNullable, IsValue};

class IsOperator implements Extension {

  public function setup($language, $emitter) {
    $pattern= function($parse, $types) use(&$pattern) {
      if ('(' === $parse->token->value || '?' === $parse->token->value) {
        $r= $types->type0($parse, false);
      } else if ('name' === $parse->token->kind) {
        $r= $types->type0($parse, false);

        if ('(' === $parse->token->value) {
          $r= new IsObjectStructure($r);
          $parse->forward();
          if (')' !== $parse->token->value) do {
            if (':' === $parse->token->value) {
              $parse->forward();
              $variable= $parse->token;
              $parse->expecting('(variable)', 'object structure');
              $member= $parse->token->value;
              array_unshift($parse->queue, $parse->token);
              $parse->token= $variable;
            } else {
              $member= $parse->token->value;
              $parse->forward();
              $parse->expecting(':', 'object structure');
            }
            $r->patterns[$member]= $pattern($parse, $types);
          } while (',' === $parse->token->value && $parse->forward() | true);
          $parse->expecting(')', 'object structure');
        } else if ('::' === $parse->token->value) {
          $parse->forward();
          $r= new IsComparable(new ScopeExpression($r->literal(), new Literal($parse->token->value)), '===');
          $parse->forward();
        }
      } else if ('string' === $parse->token->kind || 'integer' === $parse->token->kind || 'decimal' === $parse->token->kind) {
        $r= new IsComparable(new Literal($parse->token->value), '===');
        $parse->forward();
      } else if ('variable' === $parse->token->kind) {
        $parse->forward();
        $r= new IsBinding(new Variable($parse->token->value));
        $parse->forward();
      } else if ('>' === $parse->token->value || '>=' === $parse->token->value || '<' === $parse->token->value || '<=' === $parse->token->value) {
        $operator= $parse->token->value;
        $parse->forward();
        $r= new IsComparable(new Literal($parse->token->value), $operator);
        $parse->forward();
      } else if ('[' === $parse->token->value) {
        $r= new IsArrayStructure();
        $parse->forward();
        if (']' !== $parse->token->value) do {
          if ('...' === $parse->token->value) {
            $r->rest= true;
            $parse->forward();
            break;
          }

          $p= $pattern($parse, $types);
          if ('=>' === $parse->token->value) {
            $parse->forward();
            $r->patterns[$p->value->expression]= $pattern($parse, $types);
          } else {
            $r->patterns[]= $p;
          }
        } while (',' === $parse->token->value && $parse->forward() | true);
        $parse->expecting(']', 'array structure');
      } else if ('^' === $parse->token->value) {
        $parse->forward();
        $r= new IsComparable($types->expression($parse, 0), '===');
      } else {
        $parse->expecting('a type or literal', 'is');
        return null;
      }

      $operator= $parse->token->value;
      if ('|' === $operator || '&' === $operator) {
        $parse->forward();
        return new IsCompound([$r, $pattern($parse, $types)], $operator);
      } else {
        return $r;
      }
    };

    $language->infix('is', 60, function($parse, $token, $left) use($pattern) {
      return new PatternMatch($left, $pattern($parse, $this), $left->line);
    });

    $match= function($codegen, $expression, $pattern) use(&$match) {
      // \util\cmd\Console::writeLine('[...] is ', $pattern);

      if ($pattern instanceof IsLiteral) {
        $literal= $pattern->literal();
        if ('mixed' === $literal) {
          return new Literal('true');
        } else if ('true' === $literal || 'false' === $literal || 'null' === $literal) {
          return new BinaryExpression(new Literal($literal), '===', $expression);
        } else {
          return new InvokeExpression(new Literal('is_'.$literal), [$expression]);
        }
      } else if ($pattern instanceof IsValue) {
        return new InstanceOfExpression($expression, $pattern);
      } else if ($pattern instanceof IsComparable) {
        return new BinaryExpression($expression, $pattern->operator, $pattern->value);
      } else if ($pattern instanceof IsNullable) {
        $temp= new Variable($codegen->symbol());
        return new BinaryExpression(
          new BinaryExpression(new Literal('null'), '===', new Braced(new Assignment($temp, '=', $expression))),
          '||',
          $match($codegen, $temp, $pattern->element)
        );
      } else if ($pattern instanceof IsBinding) {
        return new BinaryExpression(
          new Literal('true'),
          '|',
          new Braced(new Assignment($pattern->variable, '=', $expression))
        );
      } else if ($pattern instanceof IsCompound) {
        $s= sizeof($pattern->patterns);
        if (1 === $s) return $match($codegen, $expression, $pattern->patterns[0]);

        $temp= new Variable($codegen->symbol());
        $compound= $match($codegen, new Braced(new Assignment($temp, '=', $expression)), $pattern->patterns[0]);
        for ($i= 1, $op= $pattern->operator.$pattern->operator; $i < $s; $i++) {
          $compound= new BinaryExpression($compound, $op, $match($codegen, $temp, $pattern->patterns[$i]));
        }
        return new Braced($compound);
      } else if ($pattern instanceof IsArrayStructure) {
        $null= new Literal('null');
        $temp= new Variable($codegen->symbol());
        $compound= new BinaryExpression(
          new InvokeExpression(new Literal('is_array'), [new Assignment($temp, '=', $expression)]),
          '&&',
          new BinaryExpression(
            new InvokeExpression(new Literal('sizeof'), [$temp]),
            $pattern->rest ? '>=' : '===',
            new Literal((string)sizeof($pattern->patterns))
          )
        );
        foreach ($pattern->patterns as $key => $p) {
          $compound= new BinaryExpression($compound, '&&', $match(
            $codegen,
            new Braced(new BinaryExpression(new OffsetExpression($temp, new Literal((string)$key)), '??', $null)),
            $p
          ));
        }
        return $compound;
      } else if ($pattern instanceof IsObjectStructure) {
        $temp= new Variable($codegen->symbol());
        $compound= new InstanceOfExpression(new Braced(new Assignment($temp, '=', $expression)), $pattern->type);
        foreach ($pattern->patterns as $key => $p) {
          $compound= new BinaryExpression($compound, '&&', $match(
            $codegen,
            new InstanceExpression($temp, new Literal($key)),
            $p
          ));
        }
        return $compound;
      } else {
        return new InvokeExpression(new Literal('is'), [new Literal('"'.$pattern->name().'"'), $expression]);
      }
    };

    $emitter->transform('is', function($codegen, $node) use($match) {
      return $match($codegen, $node->expression, $node->pattern);
    });
  }
}