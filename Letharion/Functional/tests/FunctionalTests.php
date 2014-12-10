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

    $this->noop = function($i) {
      return TRUE;
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

  function testGather() {
    $base_data = [2, 3, 5];
    $extra_data = [1, 2, 3, 4];

    $f = new Functional($base_data);
    $result = $f->filter($this->noop)
      ->gather(function() use ($extra_data) { return $extra_data; }, 'key_for_extra_data')
      ->filter(function($i) use ($f) {
        return in_array($i, $f->extra('key_for_extra_data'));
      })
      ->result();
    $this->assertEquals($result, [2, 3]);
  }

  function testGatherOnPreviousData() {
    $base_data = [1, 3, 5];

    $f = new Functional($base_data);
    $result = $f->filter($this->noop)
      ->gather(function($ints) {
        $ret = [];

        foreach($ints as $i) {
          $ret[] = $i + 1;
        }

        return $ret;
      }, 'key_for_extra_data')
      ->reduce(function($i, $j) use ($f) {
        static $k = 0;

        $extra = $f->extra('key_for_extra_data');

        $sum = $extra[$k++] + $i;
        return $sum + $j;
      })
      ->result();

    $this->assertEquals($result, 21);
  }

  function testExecute() {
    $array = [1, 3, 5];

    $f = new Functional($array);

    $callback = function($f = NULL) {
      static $i = 0;
      if ($i === 0 && !is_a($f, 'Letharion\Functional\Functional')) {
        throw new \Exception('Self not passed as argument');
      }
      $i++;
      return $i;
    };

    $result = $f->filter($this->noop)
      ->execute($callback)
      ->filter($this->noop);

    $this->assertEquals($callback(), 2);
  }
}
