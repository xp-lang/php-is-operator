<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

/** @see https://wiki.php.net/rfc/pattern-matching#numeric_comparison_pattern */
class NumericComparisonTest extends EmittingTest {

  /** @return iterable */
  private function fixtures() {
    yield ['1 is >0', true];
    yield ['1 is >=1', true];
    yield ['1 is <=1', true];
    yield ['0 is <1', true];
    yield ['1 is <1', false];

    yield ['1.0 is >0.0', true];
    yield ['1.0 is >=1.0', true];
    yield ['1.0 is <=1.0', true];
    yield ['0.0 is <1.0', true];
    yield ['1.0 is <1.0', false];

    yield ['"1" is >0', true];
    yield ['"1e2" is >=100', true];
    yield ['"3.141" is >3 & <4', true];

    yield ['null is >0', false];
    yield ['[] is >0', false];
    yield ['"test" is >0', false];
    yield ['((object)[]) is >0', false];
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