<?php namespace lang\ast\syntax\php;

use lang\ast\Type;
use util\Objects;

class IsObjectStructure extends Type {
  public $type, $patterns;

  /**
   * Creates a object structure "type"
   *
   * @param  lang.ast.Type $type
   * @param  lang.ast.Type[] $patterns
   */
  public function __construct($type, $patterns= []) {
    $this->type= $type;
    $this->patterns= $patterns;
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.Objects::stringOf($this->type).' '.Objects::stringOf($this->patterns).')';
  }
}