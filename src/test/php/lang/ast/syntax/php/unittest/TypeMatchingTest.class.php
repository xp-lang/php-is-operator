<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

/** @see https://wiki.php.net/rfc/pattern-matching#type_pattern */
class TypeMatchingTest extends EmittingTest {

  /** @return iterable */
  private function fixtures() {
    yield ['"" is string', true];
    yield ['"test" is string', true];
    yield ['null is string', false];

    yield ['0 is int', true];
    yield ['-1 is int', true];
    yield ['null is int', false];

    yield ['0.0 is float', true];
    yield ['-1.5 is float', true];
    yield ['null is float', false];
    yield ['null is ?float', true];

    yield ['true is bool', true];
    yield ['false is bool', true];
    yield ['null is bool', false];

    yield ['[] is array', true];
    yield ['[1, 2, 3] is array', true];
    yield ['["one" => 1] is array', true];
    yield ['null is array', false];

    yield ['[] is array<int>', true];
    yield ['[1, 2, 3] is array<int>', true];
    yield ['["key"] is array<int>', false];
    yield ['[] is array<string, int>', true];
    yield ['["one" => 1] is array<string, int>', true];
    yield ['[1] is array<string, int>', false];
    yield ['[1, "test"] is array<mixed>', true];
    yield ['[1, "test"] is array<int|string>', true];
    yield ['[null, "test"] is array<int|string>', false];

    yield ['$this is object', true];
    yield ['new Date() is object', true];
    yield ['(fn() => true) is object', true];
    yield ['((object)[]) is object', true];
    yield ['null is object', false];

    yield ['$this is callable', true];
    yield ['(fn() => true) is callable', true];
    yield ['"strlen" is callable', true];
    yield ['[$this, "run"] is callable', true];
    yield ['$this->run(...) is callable', true];
    yield ['null is callable', false];

    yield ['(fn() => true) is function(): mixed', true];
    yield ['function() { } is function(): mixed', true];
    yield ['[$this, "run"] is function(): mixed', true];
    yield ['$this->run(...) is function(): mixed', true];
    yield ['function(int $a): string { } is function(int): string', true];
    yield ['null is function(): mixed', false];

    yield ['[] is iterable', true];
    yield ['[1, 2, 3] is iterable', true];
    yield ['["key" => "value"] is iterable', true];
    yield ['$this is iterable', true];
    yield ['(function() { yield true; })() is iterable', true];
    yield ['null is iterable', false];

    yield ['$this is ?object', true];
    yield ['$this is ?Runnable', true];
    yield ['null is ?object', true];
    yield ['"test" is ?object', false];

    yield ['new Date() is Date', true];
    yield ['$this is IteratorAggregate', true];
    yield ['$this is Runnable', true];
    yield ['$this is Date', false];

    yield ['$this is self', true];
    yield ['new Date() is self', false];
    yield ['null is self', false];
  }

  #[Test, Values(from: 'fixtures')]
  public function test($expr, $expected) {
    Assert::equals($expected, $this->run('
      use util\\Date; use lang\\Runnable;

      class %T implements Runnable, IteratorAggregate {

      public function getIterator(): Traversable {
        yield true;
      }

      public function __invoke($args) {
        // NOOP
      }

      public function run() {
        return '.$expr.';
      }
    }'));
  }

  #[Test]
  public function expression_evaluated_once() {
    Assert::equals([true, 1], $this->run('class %T {
      public function run() {
        $evaluated= 0;
        $expr= function() use(&$evaluated) {
          $evaluated++;
          return 1;
        };

        $result= $expr() is ?int;
        return [$result, $evaluated];
      }
    }'));
  }
}