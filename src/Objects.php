<?php
declare(strict_types = 1);

namespace Dagr;

final class Objects
{
    private function __construct()
    {
    }

    public static function equals($a, $b) : bool
    {
        if (null === $a && null === $b) {
            return true;
        }

        if (null === $a xor null === $b) {
            return false;
        }

        if (method_exists($a, 'equals')) {
            return $a->equals($b);
        }

        return false;
    }
}
