<?php namespace lang\ast\syntax\php;

use lang\ast\Type;
use util\Objects;

class IsArrayStructure extends Type {
  public $patterns;
  public $rest= false;

  public function __construct($patterns= []) {
    $this->patterns= $patterns;
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.($this->rest ? '>=' : '===').' '.Objects::stringOf($this->patterns).')';
  }
}