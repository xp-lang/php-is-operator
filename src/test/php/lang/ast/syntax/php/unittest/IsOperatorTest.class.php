<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

class IsOperatorTest extends EmittingTest {

  #[Test]
  public function precedence() {
    $r= $this->run('class %T {
      public function run() {
        $arg= "Test";
        return $arg is string ? sprintf("string <%s>", $arg) : typeof($arg)->literal();
      }
    }');

    Assert::equals('string <Test>', $r);
  }

  #[Test, Values([[1, 'int'], ['test', 'string']])]
  public function with_match_statement($arg, $expected) {
    $r= $this->run('class %T {
      public function run(string|int $arg) {
        return match {
          $arg is string => "string",
          $arg is int => "int",
        };
      }
    }', $arg);

    Assert::equals($expected, $r);
  }
}