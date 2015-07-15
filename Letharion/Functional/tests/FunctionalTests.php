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

    $this->longer_than = function($i, $min_len) {
      return strlen($i) > $min_len;
    };
  }

  function testReduce() {
    $a = [1, 2, 3, 4];

    $f = new Functional($a);
    $r = $f->reduce($this->summer)->result();

    $this->assertEquals(10, $r);
  }

  function testWalk() {
    $a = [1, 2, 3, 4];

    $f = new Functional($a);
    $r = $f->walk($this->doubler)->result();

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
    $b = [ 'abc', 'b', 'cd', 'efgh', 'qwerty' ];

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

    $f = new Functional($b);
    $r = $f->filter($this->longer_than, 5)
      ->result();
    $this->assertEquals($r, [ 4 => 'qwerty' ]);
  }

  function testOr() {
    $a = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13 ];

    // Test a simple or filtering.
    $f = new Functional($a);
    $r = $f
      ->filter_or()
        ->filter(function($i) { return $i % 5 === 0; })
        ->filter(function($i) { return $i % 3 === 0; })
      ->result();
    $this->assertEquals($r, [2 => 3, 4 => 5, 5 => 6, 8 => 9, 9 => 10, 11 => 12 ]);

    // Test or filtering with filtering afterwards.
    $f = new Functional($a);
    $r = $f
      ->filter_or()
        ->filter(function($i) { return $i % 5 === 0; })
        ->filter(function($i) { return $i % 3 === 0; })
      ->filter(function($i) { return $i % 4 === 0; })
      ->result();
    $this->assertEquals($r, [ 11 => 12 ]);

    // Test a not() inside an or filter.
    $f = new Functional($a);
    $r = $f
      ->filter_or()
        ->filter(function($i) { return $i % 5 === 0; })
        ->not()
        ->filter(function($i) { return $i % 2 === 0; })
      ->result();
    $this->assertEquals($r, [0 => 1, 2 => 3, 4 => 5, 6 => 7, 8 => 9, 9 => 10, 10 => 11, 12 => 13]);
  }

  function testNot() {
    $a = [1, 'a', 2, 'b', 3, 'c', 4, 'd'];

    $f = new Functional($a);
    $r = $f->not()
      ->filter('is_numeric')
      ->result();
    $this->assertEquals($r, [1 => 'a', 3 => 'b', 5 => 'c', 7 => 'd']);

    $f = new Functional($a);
    $r = $f
      ->not()
      ->not()
      ->filter(function($i) { return !is_numeric($i); })
      ->result();
    $this->assertEquals($r, [1 => 'a', 3 => 'b', 5 => 'c', 7 => 'd']);

    $f = new Functional($a);
    $r = $f->filter('is_numeric')
      ->not()
      ->filter(function($i) { return $i % 2 === 0; })
      ->result();
    $this->assertEquals($r, [0 => 1, 4 => 3]);
  }

  function testExtraArguments() {
    $a = [ 'abc', 'b', 'cd', 'efgh', 'qwerty' ];
    $b = [ 1, 2, 3, 4 ];

    $f = new Functional($a);
    $r = $f
      ->filter(function($i, $j) { return strlen($i) > $j; }, 5)
      ->result();
    $this->assertEquals($r, [4 => 'qwerty']);

    $f = new Functional($a);
    $r = $f
      ->not()
      ->filter(function($i, $j) { return strlen($i) > $j; }, 5)
      ->result();
    $this->assertEquals($r, [ 'abc', 'b', 'cd', 'efgh' ]);

    $f = new Functional($b);
    $r = $f
      ->walk(function(&$i, $j) { $i += $j; }, 5)
      ->result();
    $this->assertEquals($r, [ 6, 7, 8, 9 ]);
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

    $object = new someClass();
    $result = $f->filter($this->noop)
      ->execute(array($object, 'someMethod'))
      ->filter($this->noop);

    $this->assertEquals($object->someMethod(), 2);
  }

  function testOverrideResultInExecute() {
    $a = [1, 2, 3, 4];

    $f = new Functional($a);
    $r = $f
      ->walk($this->doubler)
      ->execute(function($self) {
        $self->setResult([987]);
      })
      ->result();

    $this->assertEquals([987], $r);
  }
}

class someClass {
  function someMethod($f = NULL) {
    static $i = 0;
    if ($i === 0 && !is_a($f, 'Letharion\Functional\Functional')) {
      throw new \Exception('Self not passed as argument');
    }
    $i++;
    return $i;
  }
}
