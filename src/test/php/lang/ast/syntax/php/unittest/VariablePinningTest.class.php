<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

/** @see https://wiki.php.net/rfc/pattern-matching#variable_pinning */
class VariablePinningTest extends EmittingTest {

  /** @return iterable */
  private function fixtures() {
    yield ['$y= 2; return new Point(1, 2) is Point(x: 1, y: ^$y)', true];
    yield ['$y= 2; return new Point(1, 0) is Point(x: 1, y: ^$y)', false];

    yield ['return new Point(1, 0) is Point(x: 1, y: ^self::ZERO)', true];
    yield ['return new Point(1, 2) is Point(x: 1, y: ^self::ZERO)', false];

    yield ['$y= 2; return [1, 2] is [1, ^$y]', true];
    yield ['$y= 2; return [1, 0] is [1, ^$y]', false];

    yield ['return PHP_INT_MAX is ^PHP_INT_MAX', true];
    yield ['return [1, 0] is [1, ^self::ZERO]', true];
    yield ['return [1, 2] is [1, ^self::ZERO]', false];
  }

  #[Test, Values(from: 'fixtures')]
  public function test($expr, $expected) {
    Assert::equals($expected, $this->run('use lang\\Value, lang\\ast\\syntax\\php\\unittest\\Point; class %T {
      const ZERO= 0;

      public function run() {
        '.$expr.';
      }
    }'));
  }
}