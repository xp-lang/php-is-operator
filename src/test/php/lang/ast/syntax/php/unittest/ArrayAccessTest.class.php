<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

class ArrayAccessTest extends EmittingTest {

  /** @return iterable */
  private function fixtures() {
    yield ['$this is [$a, $b] ? [$a, $b] : null', null];
    yield ['$this is [$a, $b, true] ? [$a, $b] : null', null];
    yield ['$this is [$a, $b, $c] ? [$a, $b, $c] : null', [1, 2, 3]];

    yield ['$this is [...$rest] ? $rest : null', [1, 2, 3]];
    yield ['$this is [$a, $b, ...] ? [$a, $b] : null', [1, 2]];
    yield ['$this is [$a, ...$rest] ? [$a, $rest] : null', [1, [2, 3]]];
    yield ['$this is [$a, $b, $c, ...$rest] ? [$a, $b, $c, $rest] : null', [1, 2, 3, []]];
  }

  #[Test, Values(from: 'fixtures')]
  public function integrates_with_array_access($expr, $expected) {
    Assert::equals($expected, $this->run('class %T implements ArrayAccess, IteratorAggregate, Countable {

      public function count(): int { return 3; }

      public function offsetExists($i): bool { return true; }

      public function offsetGet($i): mixed { return $i + 1; }

      public function offsetSet($i, $value): void { /* NOOP */ }

      public function offsetUnset($i): void { /* NOOP */ }

      public function getIterator(): Traversable { yield from [1, 2, 3]; }

      public function run() {
        return '.$expr.';
      }
    }'));
  }
}