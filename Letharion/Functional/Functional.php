<?php

namespace Letharion\Functional;

class Functional {
  protected $result;

  public function __construct($array = NULL) {
    $this->result = $array;
  }

  public function reduce($callback, $i = NULL) {
    if ($i === NULL) {
      $i = $this->result;
    }

    $result = array_reduce($i, $callback);
    $this->result = $result;
    return $this;
  }

  public function walk($callback, $i = NULL) {
    if ($i === NULL) {
      $i = $this->result;
    }

    array_walk($i, $callback);
    $this->result = $i;
    return $this;
  }

  public function result() {
    return $this->result;
  }
}
