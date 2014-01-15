Composable functional programming in PHP

This library acts as a wrapper around several of PHP's built in array operators to provide an easy way to [compose][1] them.

Example:

    <?php

    require_once 'vendor/autoload.php';

    use Letharion\Functional\Functional as F;

    $a = [1, 2, 3, 4];

    $f = new F($a);
    $result = $f->walk(function(&$i) { return $i *= 2; })
      ->reduce(function($i, $j) { return $i + $j; })
      ->result();

    var_dump($result);

    int(20)

   [1]: http://en.wikipedia.org/wiki/Function_composition_%28computer_science%29

