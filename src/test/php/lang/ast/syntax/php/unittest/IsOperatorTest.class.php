<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

class IsOperatorTest extends EmittingTest {

  #[Test]
  public function is_mixed_type() {
    Assert::true($this->run('class %T {
      public function run() {
        return $this is mixed;
      }
    }'));
  }

  #[Test]
  public function precedence() {
    Assert::equals('string <Test>', $this->run('class %T {
      public function run() {
        $arg= "Test";
        return $arg is string ? sprintf("string <%s>", $arg) : typeof($arg)->literal();
      }
    }'));
  }

  #[Test, Values([[1, 'int'], ['test', 'string']])]
  public function with_match_statement($arg, $expected) {
    Assert::equals($expected, $this->run('class %T {
      public function run(string|int $arg) {
        return match {
          $arg is string => "string",
          $arg is int => "int",
        };
      }
    }', $arg));
  }

  #[Test, Values([[1, 'int'], ['test', 'string']])]
  public function match_is_variant($arg, $expected) {
    Assert::equals($expected, $this->run('class %T {
      public function run(string|int $arg) {
        return match ($arg) is {
          string => "string",
          int => "int",
        };
      }
    }', $arg));
  }

  #[Test]
  public function match_is_condition_evaluated_once() {
    Assert::equals(['one', 1], $this->run('class %T {
      public function run() {
        $invoked= 0;
        $arg= function() use(&$invoked) {
          $invoked++;
          return 1;
        };
        return match ($arg()) is {
          0 => ["zero", $invoked],
          1 => ["one", $invoked],
        };
      }
    }'));
  }

  #[Test, Values([[1, true], ["one", true], [null, false]])]
  public function as_closure($arg, $expected) {
    Assert::equals($expected, $this->run('class %T {
      public function run($arg) {
        $f= fn($arg) => $arg is int|string;
        return $f($arg);
      }
    }', $arg));
  }
}