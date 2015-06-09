<?php

namespace Letharion\Functional;

class Functional {
  protected $result;
  protected $extra_data;
  protected $negate;

  public function __construct($array) {
    $this->result = $array;
  }

  public function reduce($callback) {
    $this->result = array_reduce($this->result, $callback);
    return $this;
  }

  public function walk($callback) {
    array_walk($this->result, $callback);
    return $this;
  }

  public function filter($callback, $args = NULL) {
    if ($args !== NULL) {
      if (!is_array($args)) {
        $args = array($args);
      }

      $callback = function($row) use ($callback, $args) {
        array_unshift($args, $row);
	return call_user_func_array($callback, $args);
      };
    }

    if ($this->negate === true) {
      $callback = function($row) use ($callback) {
        return !$callback($row);
      };
      $this->negate = false;
    }

    $this->result = array_filter($this->result, $callback);
    return $this;
  }

  public function not() {
    $this->negate = !$this->negate;
    return $this;
  }

  public function result() {
    return $this->result;
  }

  public function setResult($result) {
    $this->result = $result;
  }

  public function gather($callback, $key) {
    $this->extra[$key] = $callback($this->result);
    return $this;
  }

  public function extra($key) {
    return $this->extra[$key];
  }

  public function execute($callback) {
    // 5.3 isn't officially supported, but if it doesn't require more than this...
    if (strpos(PHP_VERSION, '5.3') === 0 && is_array($callback)) {
      $callback[0]->$callback[1]($this);
    }
    else {
      $callback($this);
    }
    return $this;
  }
}
