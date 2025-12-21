<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

/** @see https://wiki.php.net/rfc/pattern-matching#literal_pattern */
class LiteralTest extends EmittingTest {

  /** @return iterable */
  private function fixtures() {
    yield ['"test" is "test"', true];
    yield ['true is true', true];
    yield ['false is false', true];
    yield ['null is null', true];
    yield ['1 is 1', true];
    yield ['1.5 is 1.5', true];

    yield ['null is "test"', false];
    yield ['0 is ""', false];
    yield ['0 is "0"', false];
    yield ['1 is "1"', false];
    yield ['true is 1', false];
    yield ['false is 0', false];

    yield ['0 is self::ZERO', true];
    yield ['1 is self::ZERO', false];
  }

  #[Test, Values(from: 'fixtures')]
  public function test($expr, $expected) {
    Assert::equals($expected, $this->run('class %T {
      const ZERO= 0;

      public function run() {
        return '.$expr.';
      }
    }'));
  }
}