<?php namespace lang\ast\syntax\php;

use lang\IllegalStateException;
use lang\ast\nodes\{
  ArrayLiteral,
  Assignment,
  BinaryExpression,
  Braced,
  InstanceExpression,
  InstanceOfExpression,
  InvokeExpression,
  Literal,
  MatchCondition,
  MatchExpression,
  OffsetExpression,
  ScopeExpression,
  TernaryExpression,
  Variable
};
use lang\ast\syntax\Extension;
use lang\ast\types\{IsArray, IsLiteral, IsFunction, IsMap, IsUnion, IsIntersection, IsNullable, IsValue};
use lang\ast\{Node, Token};

class IsOperator implements Extension {

  public function setup($language, $emitter) {
    $pattern= function($parse, $types) use(&$pattern) {
      if ('(' === $parse->token->value) {
        $parse->forward();
        $r= $pattern($parse, $types);
        $parse->expecting(')', 'dnf');
      } else if ('?' === $parse->token->value) {
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
          $r= new IsIdentical(new ScopeExpression($r->literal(), new Literal($parse->token->value)));
          $parse->forward();
        }
      } else if ('string' === $parse->token->kind || 'integer' === $parse->token->kind || 'decimal' === $parse->token->kind) {
        $r= new IsIdentical(new Literal($parse->token->value));
        $parse->forward();
      } else if ('variable' === $parse->token->kind) {
        $parse->forward();
        $r= new IsBinding(new Variable($parse->token->value));
        $parse->forward();
      } else if ('>' === $parse->token->value || '>=' === $parse->token->value || '<' === $parse->token->value || '<=' === $parse->token->value) {
        $operator= $parse->token->value;
        $parse->forward();
        $r= new IsComparison(new Literal($parse->token->value), $operator);
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
        $r= new IsIdentical($types->expression($parse, 0));
      } else {
        $parse->expecting('a type or literal', 'is');
        return null;
      }

      $operator= $parse->token->value;
      if ('|' === $operator || '&' === $operator) {
        $parse->forward();
        $n= $pattern($parse, $types);

        // Merge compound types with the same operators, keeping evaluation order
        if ($n instanceof IsCompound && $operator === $n->operator) {
          array_unshift($n->patterns, $r);
          return $n;
        } else {
          return new IsCompound([$r, $n], $operator);
        }
      } else {
        return $r;
      }
    };

    $language->infix('is', 60, function($parse, $token, $left) use($pattern) {
      return new PatternMatch($left, $pattern($parse, $this), $left->line);
    });

    $language->prefix('match', 0, function($parse, $token) use($pattern) {
      $patterns= false;
      $condition= null;

      if ('(' === $parse->token->value) {
        $parse->forward();
        $condition= $this->expression($parse, 0);
        $parse->expecting(')', 'match');

        // See https://wiki.php.net/rfc/pattern-matching#match_is_placement
        if ('is' === $parse->token->value) {
          $parse->forward();
          $patterns= true;
        }
      }

      $default= null;
      $cases= [];
      $parse->expecting('{', 'match');
      while ('}' !== $parse->token->value) {
        if ('default' === $parse->token->value) {
          $parse->forward();
          $parse->expecting('=>', 'match');
          $default= $this->expression($parse, 0);
        } else if ($patterns) {
          $match= [new PatternMatch($condition, $pattern($parse, $this), $parse->token->line)];
          $parse->expecting('=>', 'match');
          $cases[]= new MatchCondition($match, $this->expression($parse, 0), $parse->token->line);
        } else {
          $match= [];
          do {
            $match[]= $this->expression($parse, 0);
          } while (',' === $parse->token->value && $parse->forward() | true);
          $parse->expecting('=>', 'match');
          $cases[]= new MatchCondition($match, $this->expression($parse, 0), $parse->token->line);
        }

        if (',' === $parse->token->value) {
          $parse->forward();
        }
      }
      $parse->expecting('}', 'match');

      return new MatchExpression($patterns ? null : $condition, $cases, $default, $token->line);
    });

    $match= function($codegen, $expression, $pattern) use(&$match) {

      // Basic type matching, literal comparison and variable binding
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
      } else if ($pattern instanceof IsArray || $pattern instanceof IsMap || $pattern instanceof IsFunction) {
        return new InvokeExpression(new Literal('is'), [new Literal('"'.$pattern->name().'"'), $expression]);
      } else if ($pattern instanceof IsIdentical) {
        return new BinaryExpression($expression, '===', $pattern->value);
      } else if ($pattern instanceof IsBinding) {
        $true= new Literal('true');
        return new Braced(new TernaryExpression(
          new Braced(new Assignment($pattern->variable, '=', $expression)),
          $true,
          $true
        ));
      }

      // Ensure expressions are only evaluated once.
      if ($expression instanceof Variable) {
        $use= $init= $expression;
      } else {
        $use= new Variable($codegen->symbol());
        $init= new Braced(new Assignment($use, '=', $expression));
      }

      if ($pattern instanceof IsComparison) {
        return new BinaryExpression(
          new InvokeExpression(new Literal('is_numeric'), [$init]),
          '&&',
          new BinaryExpression($use, $pattern->operator, $pattern->value)
        );
      } else if ($pattern instanceof IsNullable) {
        return new BinaryExpression(
          new BinaryExpression(new Literal('null'), '===', $init),
          '||',
          $match($codegen, $use, $pattern->element)
        );
      } else if ($pattern instanceof IsCompound) {
        $compound= $match($codegen, $init, $pattern->patterns[0]);
        for ($i= 1, $s= sizeof($pattern->patterns), $op= $pattern->operator.$pattern->operator; $i < $s; $i++) {
          $compound= new BinaryExpression($compound, $op, $match($codegen, $use, $pattern->patterns[$i]));
        }
        return new Braced($compound);
      } else if ($pattern instanceof IsObjectStructure) {
        $compound= new InstanceOfExpression($init, $pattern->type);
        foreach ($pattern->patterns as $key => $p) {
          $compound= new BinaryExpression($compound, '&&', $match(
            $codegen,
            new InstanceExpression($use, new Literal($key)),
            $p
          ));
        }
        return $compound;
      } else if ($pattern instanceof IsArrayStructure) {
        $compound= new BinaryExpression(
          new InvokeExpression(new Literal('is_array'), [$init]),
          '&&',
          new BinaryExpression(
            new InvokeExpression(new Literal('sizeof'), [$use]),
            $pattern->rest ? '>=' : '===',
            new Literal((string)sizeof($pattern->patterns))
          )
        );
        foreach ($pattern->patterns as $key => $p) {
          $offset= new Literal((string)$key);
          $compound= new BinaryExpression($compound, '&&', new BinaryExpression(
            new InvokeExpression(new Literal('array_key_exists'), [$offset, $use]),
            '&&',
            $match($codegen, new OffsetExpression($use, $offset), $p),
          ));
        }
        return $compound;
      }

      // Should be unreachable
      throw new IllegalStateException('Unsupported pattern '.$pattern->toString());
    };

    $emitter->transform('is', function($codegen, $node) use($match) {
      return $match($codegen, $node->expression, $node->pattern);
    });
  }
}