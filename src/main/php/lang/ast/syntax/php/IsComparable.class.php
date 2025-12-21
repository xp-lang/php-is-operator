<?php namespace lang\ast\syntax\php;

use lang\ast\Type;
use util\Objects;

class IsComparable extends Type {
  public $value, $operator;

  /**
   * Creates a comparable "type"
   *
   * @param  lang.ast.Type[] $patterns
   * @param  string $operator
   */
  public function __construct($value, $operator) {
    $this->value= $value;
    $this->operator= $operator;
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.$this->operator.' '.Objects::stringOf($this->value).')';
  }
}