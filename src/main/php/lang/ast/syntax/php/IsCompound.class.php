<?php namespace lang\ast\syntax\php;

use lang\ast\Type;
use util\Objects;

class IsCompound extends Type {
  public $patterns, $operator;

  /**
   * Creates a compound "type"
   *
   * @param  lang.ast.Type[] $patterns
   * @param  string $operator
   */
  public function __construct($patterns, $operator) {
    $this->patterns= $patterns;
    $this->operator= $operator;
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.$this->operator.' '.Objects::stringOf($this->patterns).')';
  }
}