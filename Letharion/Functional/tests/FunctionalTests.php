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

    $this->is_even = function($i) {
      return $i % 2 === 0;
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

    $f = new Functional($a);
    $r = $f->filter($this->is_even)
      ->walk($this->doubler)
      ->reduce($this->summer)
      ->result();

    $this->assertEquals($r, 12);
  }

  function testFilter() {
    $a = [1, 'a', 2, 'b', 3, 'c', 4, 'd'];

    $f = new Functional($a);
    $r = $f->filter('is_numeric')
      ->result();
    $this->assertEquals($r, [0 => 1, 2 => 2, 4 => 3, 6 => 4]);

    $f = new Functional($a);
    $r = $f->filter(function($i) { return !is_numeric($i); })
      ->result();
    $this->assertEquals($r, [1 => 'a', 3 => 'b', 5 => 'c', 7 => 'd']);

    $f = new Functional($a);
    $r = $f->filter('is_numeric')
      ->filter(function($i) { return $i % 2 === 0; })
      ->result();
    $this->assertEquals($r, [2 => 2, 6 => 4]);
  }
}
