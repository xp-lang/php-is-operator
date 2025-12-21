<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

/** @see https://wiki.php.net/rfc/pattern-matching#array_structure_pattern */
class ArrayStructureTest extends EmittingTest {

  /** @return iterable */
  private function fixtures() {
    yield ['[] is []', true];
    yield ['[1, 2] is []', false];
    yield ['null is []', false];

    yield ['[0] is [0]', true];
    yield ['[1] is [1]', true];
    yield ['[2] is [1]', false];
    yield ['[] is [1]', false];
    yield ['[1, 2] is [1]', false];
    yield ['null is [1]', false];

    yield ['[1, 2] is [1, 2]', true];
    yield ['[1] is [1, 2]', false];
    yield ['[] is [1, 2]', false];
    yield ['null is [1, 2]', false];

    yield ['[1] is [...]', true];
    yield ['[1, 2] is [...]', true];
    yield ['[1, 2] is [1, 2, ...]', true];
    yield ['[1, 2, 3] is [1, 2, ...]', true];
    yield ['[0, 1, 2] is [0, 1, ...]', true];

    yield ['["one" => 1, "two" => 2] is ["one" => 1, "two" => 2]', true];
    yield ['["two" => 2, "one" => 1] is ["one" => 1, "two" => 2]', true];
    yield ['[1, 2] is ["one" => 1, "two" => 2]', false];
    yield ['null is ["one" => 1, "two" => 2]', false];

    yield ['["one" => 1] is ["one" => 1, ...]', true];
    yield ['["one" => 1, "two" => 2] is ["one" => 1, ...]', true];
    yield ['["two" => 2] is ["one" => 1, ...]', false];
    yield ['["two" => 2] is ["one" => null]', false];

    yield ['[2] is [0 => 2]', true];
    yield ['[2] is ["0" => 2]', true];
    yield ['[1] is [0 => 2]', false];
    yield ['[1] is ["0" => 2]', false];

    yield ['[1, 2] is [1, 2|3]', true];
    yield ['[1, 3] is [1, 2|3]', true];
    yield ['[1, 2, 3] is [1, 2|3]', false];
    yield ['[1, 2] is [1, >=0 & <=10]', true];

    yield ['[1, 2] is [1, int|string]', true];
    yield ['[1, "2"] is [1, int|string]', true];
    yield ['[1, null] is [1, int|string]', false];
  }

  #[Test, Values(from: 'fixtures')]
  public function test($expr, $expected) {
    Assert::equals($expected, $this->run('class %T {
      public function run() {
        return '.$expr.';
      }
    }'));
  }
}