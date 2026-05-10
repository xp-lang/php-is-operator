<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

/** @see https://wiki.php.net/rfc/pattern-matching#object_pattern */
class ObjectStructureTest extends EmittingTest {

  /** @return iterable */
  private function fixtures() {
    yield ['new Point(1, 2, 0) is Point', true];
    yield ['$this is Point', false];
    yield ['null is Point', false];

    yield ['new Point(1, 2, 0) is Point(x: 1)', true];
    yield ['new Point(1, 2, 0) is Point(y: 2)', true];
    yield ['new Point(1, 2, 0) is Point(z: 0)', true];
    yield ['new Point(1, 2, 0) is Point(x: 1, y: 2)', true];
    yield ['new Point(1, 2, 0) is Point(x: 1, y: 2, z: 0)', true];
    yield ['new Point(1, 2, 3) is Point(x: 1, y: 2, z: 0)', false];
    yield ['$this is Point(x: 1, y: 2)', false];
    yield ['null is Point(x: 1, y: 2)', false];

    yield ['new Point(1, 2) is Point(x: 1, y: 2, z: >=0)', true];
    yield ['new Point(1, 2) is Point(x: 1, y: 2|3)', true];
    yield ['new Point(1, 2) is Point(x: 1, y: mixed)', true];

    yield ['new Point(1, 2) is Point(x: 1)&Value', true];
    yield ['new Point(1, 2) is Point(x: 1)|null', true];
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