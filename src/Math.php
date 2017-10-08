<?php
declare(strict_types = 1);

namespace Dagr;

use Dagr\Exception\ArithmeticException;

final class Math
{
    private function __construct()
    {
    }

    public static function floorMod(int $x, int $y) : int
    {
        return $x - self::floorDiv($x, $y) * $y;
    }

    public static function floorDiv(int $x, int $y) : int
    {
        $r = intdiv($x, $y);

        if (($x ^ $y) < 0 && ($r * $y !== $x)) {
            --$r;
        }

        return $r;
    }

    public static function addExact(int $x, int $y) : int
    {
        $r = $x + $y;

        if ((($x ^ $r) & ($y ^ $r)) < 0) {
            throw new ArithmeticException('Integer overflow');
        }

        return $r;
    }

    public static function subtractExact(int $x, int $y) : int
    {
        $r = $x - $y;

        if ((($x ^ $y) & ($x ^ $r)) < 0) {
            throw new ArithmeticException('Integer overflow');
        }

        return $r;
    }

    public static function multiplyExact(int $x, int $y) : int
    {
        $r = $x * $y;

        if ((int) $r !== $r) {
            throw new ArithmeticException('Integer overflow');
        }

        return $r;
    }
}
