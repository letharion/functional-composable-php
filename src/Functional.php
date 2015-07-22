<?php

namespace Letharion\Functional;

class Functional {
  protected $result;
  protected $extra_data;
  protected $negate;
  protected $or_callback1;
  protected $or;

  public function __construct($array) {
    $this->or = NULL;
    $this->result = $array;
  }

  public function reduce($callback) {
    $this->result = array_reduce($this->result, $callback);
    return $this;
  }

  public function walk($callback, $args = NULL) {
    if ($args !== NULL) {
      $original_callback = $callback;
      if (!is_array($args)) {
        $args = array($args);
      }

      $callback = function(&$row) use ($original_callback, $args) {
        // Work around the fact that call_user_func_array won't pass as reference.
        $ref_args = array(&$row);
        foreach($args as $k => &$arg) {
          $ref_args[$k + 1] = $arg;
        }
	      call_user_func_array($original_callback, $ref_args);
      };
    }

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
        // @TODO Try to get rid of when PHP 5.3 support is no longer needed.
        return !call_user_func_array($callback, array($row));
      };
      $this->negate = false;
    }

    if ($this->or !== NULL) {
      if ($this->or === 1) {
        $this->or_callback1 = $callback;
        $this->or++;
        return $this;
      }
      else {
        $or_callback1 = $this->or_callback1;
        $or_callback2 = $callback;
        $callback = function($row) use ($or_callback1, $or_callback2) {
          return $or_callback1($row) || $or_callback2($row);
        };
        $this->or = NULL;
      }
    }

    $this->result = array_filter($this->result, $callback);
    return $this;
  }

  public function filter_or() {
    $this->or = 1;
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
