<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

/** @see https://wiki.php.net/rfc/pattern-matching#variable_binding */
class VariableBindingTest extends EmittingTest {

  /** @return iterable */
  private function fixtures() {
    yield ['new Point(1, 2, 3) is Point(z: $z) ? $z : null', 3];
    yield ['new Point(1, 2, 3) is Point(:$z) ? $z : null', 3];
    yield ['new Point(1, 2, 3) is Point(z: $bound) ? $bound : null', 3];

    yield ['new Point(1, 2, 3) is Point(x: 1, y: 2, z: $z) ? $z : null', 3];
    yield ['new Point(1, 2, 3) is Point(x: 0, y: 2, z: $z) ? $z : null', null];

    yield ['new Point(1, 2, 3) is Point(x: $x, y: $y, z: $z) ? [$x, $y, $z] : null', [1, 2, 3]];
    yield ['new Point(1, 2, 3) is Point(:$x, :$y, :$z) ? [$x, $y, $z] : null', [1, 2, 3]];

    yield ['new Point(1, 2, 3) is Point(:$z & ?int) ? $z : null', 3];
    yield ['new Point(1, 2, 3) is Point(:$x, :$y, :$z & > 0) ? [$x, $y, $z] : null', [1, 2, 3]];
    yield ['new Point(1, 2, 0) is Point(:$x, :$y, :$z & > 0) ? [$x, $y, $z] : null', null];

    yield ['[1, 2, 3] is [1, 2, $z] ? $z : null', 3];
    yield ['[0, 2, 3] is [1, 2, $z] ? $z : null', null];
    yield ['[1, 2, 3] is [$x, $y, $z] ? [$x, $y, $z] : null', [1, 2, 3]];
    yield ['[0, 1, 2] is [$x, $y, $z] ? [$x, $y, $z] : null', [0, 1, 2]];

    yield ['["one" => 1, "two" => 2] is ["one" => 1, "two" => $t] ? $t : null', 2];
    yield ['["one" => 0, "two" => 2] is ["one" => 1, "two" => $t] ? $t : null', null];
  }

  #[Test, Values(from: 'fixtures')]
  public function test($expr, $expected) {
    Assert::equals($expected, $this->run('use lang\\Value, lang\\ast\\syntax\\php\\unittest\\Point; class %T {
      public function run() {
        return '.$expr.';
      }
    }'));
  }
}