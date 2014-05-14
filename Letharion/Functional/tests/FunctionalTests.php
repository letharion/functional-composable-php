<?php
/**
 * @file
 * Tests for Letharion\Functional.
 */

use Letharion\Functional\Functional;

class FunctionalTest extends \PHPUnit_Framework_TestCase {
  
  protected $doubler;
  protected $summer;

  function setUp() {
    $this->doubler = function (&$i) {
      $i *= 2;
    };

    $this->summer = function ($i, $j) {
      return $i + $j;
    };
  }

  function testReduce() {
    $a = [1, 2, 3, 4];

    $f = new Functional();
    $r = $f->reduce($this->summer, $a)->result();

    $this->assertEquals(10, $r);
  }

  function testWalk() {
    $a = [1, 2, 3, 4];

    $f = new Functional();
    $r = $f->walk($this->doubler, $a)->result();

    $this->assertEquals([2, 4, 6, 8], $r);
  }

  function testCompose() {
    $a = [1, 2, 3, 4];

    $f = new Functional($a);
    $r = $f->walk($this->doubler)
      ->reduce($this->summer)
      ->result();

    $this->assertEquals($r, 20);
  }

  function testJoin() {
    $a = [ [1, 'a' ], [2, 'b'], [3, 'c'] ];
    $b = [ [2, 'e' ], [3, 'f'], [4, 'g'] ];

    $expectation = [ [2, 'b', 'e'], [3, 'c', 'f'] ];

    $f = new Functional($a);
    $result = $f->join(NULL, $b)
      ->result();

    $assert_f = new Functional($expectation)  ;
    $this->assertEquals($expectation, $result);
  }
}
