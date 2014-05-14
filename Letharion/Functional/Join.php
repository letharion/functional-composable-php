<?php

namespace Letharion\Functional;

use Letharion\Functional\Functional as F;

function join($left, $right, $options = array()) {
  $left_f = new F($left);
  $left_r = new F($right);
  $result = array();
  $left_f->walk(function ($left_row) {
    $right_f->walk(function ($right_row) use (&$result, $left_row) {
      echo $left_row . PHP_EOL;
      echo $right_row . PHP_EOL;
      die;
    });
  });
}
