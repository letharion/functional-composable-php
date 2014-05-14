<?php

namespace Letharion\Functional;

use Letharion\Functional\Functional as F;

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

  public function join($left, $right, $options = array()) {
    if ($left === NULL) {
      $left = $this->result;
    }

    $right_f = new F($right);
    $result = array();
    $this->walk(function ($left_row) use ($right_f) {
      $right_f->walk(function ($right_row) use (&$result, $left_row) {
        echo $left_row . PHP_EOL;
        echo $right_row . PHP_EOL;
        die;
      });
    });
  }
}
