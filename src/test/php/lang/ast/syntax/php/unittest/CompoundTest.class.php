<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

/** @see https://wiki.php.net/rfc/pattern-matching#compound_patterns */
class CompoundTest extends EmittingTest {

  /** @return iterable */
  private function fixtures() {
    yield ['1 is >0 & <10', true];
    yield ['0 is >0 & <10', false];

    yield ['1 is int|float', true];
    yield ['1.5 is int|float', true];
    yield ['"test" is int|float', false];

    yield ['1 is int & 1', true];
    yield ['2 is int & (1|2)', true];
    yield ['3 is int & float', false];
    yield ['3 is 1|2|3', true];
    yield ['"test" is string & "success"', false];

    yield ['"test" is "success"|"failure"', false];
    yield ['"success" is "success"|"failure"', true];
    yield ['"success" is "success"|"failure"|null', true];
    yield ['"success" is "success"|"failure"|"running"', true];

    yield ['[] is ""|array', true];
    yield ['[] is array|""', true];
    yield ['[] is array|null', true];
    yield ['[] is null|array', true];
    yield ['[] is [] & [...]', true];

    yield ['$this is array|Traversable', true];
    yield ['$this is IteratorAggregate|Runnable', true];
    yield ['new Date() is IteratorAggregate|Runnable', false];

    yield ['$this is IteratorAggregate&Runnable', true];
    yield ['$this is (IteratorAggregate&Runnable)', true];
    yield ['null is IteratorAggregate&Runnable', false];
    yield ['new Date() is IteratorAggregate&Runnable', false];

    yield ['$this is null|(IteratorAggregate&Runnable)', true];
    yield ['null is null|(IteratorAggregate&Runnable)', true];
    yield ['$this is (IteratorAggregate&Runnable)|null', true];
    yield ['null is (IteratorAggregate&Runnable)|null', true];
    yield ['null is IteratorAggregate&(Runnable|null)', false];

    yield ['1 is null | >0 & <10', true];
    yield ['null is null | >0 & <10', true];
    yield ['0 is null | >0 & <10', false];
  }

  #[Test, Values(from: 'fixtures')]
  public function test($expr, $expected) {
    Assert::equals($expected, $this->run('
      use util\\Date, lang\\Runnable, lang\\ast\\syntax\\php\\unittest\\Point; 

      class %T implements Runnable, IteratorAggregate {

      public function getIterator(): Traversable {
        yield true;
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

        $result= $expr() is >0 & <2;
        return [$result, $evaluated];
      }
    }'));
  }
}