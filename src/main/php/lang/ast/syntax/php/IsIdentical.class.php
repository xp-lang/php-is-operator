<?php namespace lang\ast\syntax\php;

use lang\ast\Type;
use util\Objects;

class IsIdentical extends Type {
  public $value;

  /**
   * Creates a identical "type"
   *
   * @param  lang.ast.Node $value
   */
  public function __construct($value) {
    $this->value= $value;
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.Objects::stringOf($this->value).')';
  }
}