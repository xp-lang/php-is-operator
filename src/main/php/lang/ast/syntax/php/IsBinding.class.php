<?php namespace lang\ast\syntax\php;

use lang\ast\Type;

class IsBinding extends Type {
  public $variable;
  public $restriction= null;

  /**
   * Creates a binding "type"
   *
   * @param  lang.ast.nodes.Variable $variable
   */
  public function __construct($variable) {
    $this->variable= $variable;
  }

  /** @return string */
  public function toString() {
    $restriction= $this->restriction ? ' & '.$this->restriction->toString() : '';
    return nameof($this).'('.$this->variable->pointer.$restriction.')';
  }
}