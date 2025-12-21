<?php namespace lang\ast\syntax\php;

use lang\ast\Type;

class IsBinding extends Type {
  public $variable;

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
    return nameof($this).'('.$this->variable->pointer.')';
  }
}