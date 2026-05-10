<?php namespace lang\ast\syntax\php\unittest;

use lang\Value;
use util\Objects;

class Point implements Value {
  public $x, $y, $z;

  /** Creates a new point */
  public function __construct(int $x, int $y, int $z= 0) {
    $this->x= $x;
    $this->y= $y;
    $this->z= $z;
  }

  /** @return string */
  public function hashCode() {
    return 'P'.$this->x.','.$this->y.','.$this->z;
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.$this->x.', '.$this->y.', '.$this->z.')';
  }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self
      ? Objects::compare([$this->x, $this->y, $this->z], [$value->x, $value->y, $value->z])
      : 1
    ;
  }
}