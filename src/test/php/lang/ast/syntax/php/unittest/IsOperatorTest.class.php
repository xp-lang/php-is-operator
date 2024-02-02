<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Test, Values};

class IsOperatorTest extends EmittingTest {

  #[Test]
  public function this_is_self() {
    $r= $this->run('class <T> {
      public function run() {
        return $this is self;
      }
    }');

    Assert::true($r);
  }

  #[Test]
  public function new_self_is_static() {
    $r= $this->run('class <T> {
      public function run() {
        return new self() is static;
      }
    }');

    Assert::true($r);
  }

  #[Test]
  public function is_qualified_type() {
    $r= $this->run('class <T> {
      public function run() {
        return new \util\Date() is \util\Date;
      }
    }');

    Assert::true($r);
  }

  #[Test]
  public function is_imported_type() {
    $r= $this->run('use util\Date; class <T> {
      public function run() {
        return new Date() is Date;
      }
    }');

    Assert::true($r);
  }

  #[Test]
  public function is_aliased_type() {
    $r= $this->run('use util\Date as D; class <T> {
      public function run() {
        return new D() is D;
      }
    }');

    Assert::true($r);
  }

  #[Test]
  public function is_type_variable() {
    $r= $this->run('class <T> {
      public function run() {
        $type= self::class;
        return new self() is $type;
      }
    }');

    Assert::true($r);
  }

  #[Test]
  public function is_primitive_type() {
    $r= $this->run('class <T> {
      public function run() {
        return [1 is int, true is bool, -6.1 is float, 6.1 is float, "test" is string];
      }
    }');

    Assert::equals([true, true, true, true, true], $r);
  }

  #[Test]
  public function is_nullable_type() {
    $r= $this->run('class <T> {
      public function run() {
        return [null is ?int, null is ?self, $this is ?self];
      }
    }');

    Assert::equals([true, true, true], $r);
  }

  #[Test]
  public function is_array_pseudo_type() {
    $r= $this->run('class <T> {
      public function run() {
        return [[] is array, [1, 2, 3] is array, ["key" => "value"] is array, null is array];
      }
    }');

    Assert::equals([true, true, true, false], $r);
  }

  #[Test]
  public function is_object_pseudo_type() {
    $r= $this->run('class <T> {
      public function run() {
        return [$this is object, function() { } is object, null is object];
      }
    }');

    Assert::equals([true, true, false], $r);
  }

  #[Test]
  public function is_callable_pseudo_type() {
    $r= $this->run('class <T> {
      public function run() {
        return [function() { } is callable, [$this, "run"] is callable, null is callable];
      }
    }');

    Assert::equals([true, true, false], $r);
  }

  #[Test]
  public function is_native_iterable_type() {
    $r= $this->run('class <T> implements \IteratorAggregate {
      public function getIterator(): \Traversable {
        yield 1;
      }

      public function run() {
        return [[] is iterable, $this is iterable, null is iterable];
      }
    }');

    Assert::equals([true, true, false], $r);
  }

  #[Test]
  public function is_map_type() {
    $r= $this->run('class <T> {
      public function run() {
        return [["key" => "value"] is array<string, string>, null is array<string, string>];
      }
    }');

    Assert::equals([true, false], $r);
  }

  #[Test]
  public function is_array_type() {
    $r= $this->run('class <T> {
      public function run() {
        return [["key"] is array<string>, ["key"] is array<int>, null is array<string>];
      }
    }');

    Assert::equals([true, false, false], $r);
  }

  #[Test]
  public function is_union_type() {
    $r= $this->run('class <T> {
      public function run() {
        return [1 is int|string, "test" is int|string, null is int|string];
      }
    }');

    Assert::equals([true, true, false], $r);
  }

  #[Test]
  public function is_function_type() {
    $r= $this->run('class <T> {
      public function run() {
        return [function(int $a): int { } is function(int): int, null is function(int): int];
      }
    }');

    Assert::equals([true, false], $r);
  }

  #[Test]
  public function precedence() {
    $r= $this->run('class <T> {
      public function run() {
        $arg= "Test";
        return $arg is string ? sprintf("string <%s>", $arg) : typeof($arg)->literal();
      }
    }');

    Assert::equals('string <Test>', $r);
  }

  #[Test, Values([[1, 'int'], ['test', 'string']])]
  public function with_match_statement($arg, $expected) {
    $r= $this->run('class <T> {
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