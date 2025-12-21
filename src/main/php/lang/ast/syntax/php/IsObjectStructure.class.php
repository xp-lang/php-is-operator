<?php namespace lang\ast\syntax\php;

use lang\ast\Type;
use util\Objects;

class IsObjectStructure extends Type {
  public $type, $patterns;

  public function __construct($type, $patterns= []) {
    $this->type= $type;
    $this->patterns= $patterns;
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.Objects::stringOf($this->type).' '.Objects::stringOf($this->patterns).')';
  }
}