<?php

namespace Letharion\Functional;

class Functional {
  protected $result;
  protected $extra_data;

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

  public function filter($callback, $i = NULL) {
    if ($i === NULL) {
      $i = $this->result;
    }

    $this->result = array_filter($i, $callback);
    return $this;
  }

  public function result() {
    return $this->result;
  }

  public function gather($callback, $key) {
    $this->extra[$key] = $callback($this->result);
    return $this;
  }

  public function extra($key) {
    return $this->extra[$key];
  }

  public function execute($callback) {
    $callback($this);
    return $this;
  }
}
