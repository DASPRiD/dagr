<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;

final class SignStyle
{
    use NotClonableTrait;
    use NotSerializableTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var self[]
     */
    private static $cache = [];

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Parse helper.
     */
    public function parse(bool $positive, bool $strict, bool $fixedWidth) : bool
    {
        switch ($this->name) {
            case 'normal':
                // Valid if negative or (positive and lenient)
                return !$positive || !$strict;

            case 'always':
            case 'exceedsPad':
                return true;

            default:
                // Valid if lenient and not fixed width
                return !$strict && !$fixedWidth;
        }
    }

    /**
     * Returns the name of the style.
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Style to output the sign only if the value is negative.
     *
     * In strict parsing, the negative sign will be accepted and the positive sign rejected. In lenient parsing, any
     * sign will be accepted.
     */
    public static function normal() : self
    {
        return self::$cache['normal'] ?? self::$cache['normal'] = new self('normal');
    }

    /**
     * Style to always output the sign, where zero will output '+'.
     *
     * In strict parsing, the absence of a sign will be rejected. In lenient parsing, any sign will be accepted, with
     * the absence of a sign treated as a positive number.
     */
    public static function always() : self
    {
        return self::$cache['always'] ?? self::$cache['always'] = new self('always');
    }

    /**
     * Style to never output sign, only outputting the absolute value.
     *
     * In strict parsing, any sign will be rejected. In lenient parsing, any sign will be accepted unless the width is
     * fixed.
     */
    public static function never() : self
    {
        return self::$cache['never'] ?? self::$cache['never'] = new self('never');
    }

    /**
     * Style to block negative values, throwing an exception on printing.
     *
     * In strict parsing, any sign will be rejected. In lenient parsing, any sign will be accepted unless the width is
     * fixed.
     */
    public static function notNegative() : self
    {
        return self::$cache['notNegative'] ?? self::$cache['notNegative'] = new self('notNegative');
    }

    /**
     * Style to always output the sign if the value exceeds the pad width.
     *
     * A negative value will always output the '-' sign.
     *
     * In strict parsing, the sign will be rejected unless the pad width is exceeded. In lenient parsing, any sign will
     * be accepted, with the absence of a sign treated as a positive number.
     */
    public static function exceedsPad() : self
    {
        return self::$cache['exceedsPad'] ?? self::$cache['exceedsPad'] = new self('exceedsPad');
    }
}
