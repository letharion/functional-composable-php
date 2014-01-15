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
}
