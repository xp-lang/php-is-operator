<?php namespace lang\ast\syntax\php;

use lang\ast\Node;

class PatternMatch extends Node {
  public $kind= 'is';
  public $expression, $pattern;

  public function __construct($expression, $pattern= null, $line= -1) {
    $this->expression= $expression;
    $this->pattern= $pattern;
    $this->line= $line;
  }
}