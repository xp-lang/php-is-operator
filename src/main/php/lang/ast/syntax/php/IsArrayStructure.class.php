<?php namespace lang\ast\syntax\php;

use lang\ast\Type;
use util\Objects;

class IsArrayStructure extends Type {
  public $patterns, $rest;

  /**
   * Creates a object structure "type"
   *
   * @param  lang.ast.Type[] $patterns
   * @param  bool $rest
   */
  public function __construct($patterns= [], $rest= false) {
    $this->patterns= $patterns;
    $this->rest= $rest;
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.($this->rest ? '>=' : '===').' '.Objects::stringOf($this->patterns).')';
  }
}