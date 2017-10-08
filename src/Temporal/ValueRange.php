<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

use Dagr\Exception\DateTimeException;
use Dagr\Exception\IllegalArgumentException;

final class ValueRange
{
    /**
     * @var int
     */
    private $minSmallest;

    /**
     * @var int
     */
    private $minLargest;

    /**
     * @var int
     */
    private $maxSmallest;

    /**
     * @var int
     */
    private $maxLargest;

    private function __construct(int $minSmallest, int $minLargest, int $maxSmallest, int $maxLargest)
    {
        $this->minSmallest = $minSmallest;
        $this->minLargest = $minLargest;
        $this->maxSmallest = $maxSmallest;
        $this->maxLargest = $maxLargest;
    }

    public static function ofTwo(int $min, int $max) : self
    {
        if ($min > $max) {
            throw new IllegalArgumentException('Minimum value must be less than maximum value');
        }

        return new self($min, $min, $max, $max);
    }

    public static function ofThree(int $min, int $maxSmallest, int $maxLargest) : self
    {
        return self::ofFour($min, $min, $maxSmallest, $maxLargest);
    }

    public static function ofFour(int $minSmallest, int $minLargest, int $maxSmallest, int $maxLargest) : self
    {
        if ($minSmallest > $minLargest) {
            throw new IllegalArgumentException('Smallest minimum value must be less than largest minimum value');
        }

        if ($maxSmallest > $maxLargest) {
            throw new IllegalArgumentException('Smallest maximum value must be less than largest maximum value');
        }

        if ($minLargest > $maxLargest) {
            throw new IllegalArgumentException('Minimum value must be less than maximum value');
        }

        return new self($minSmallest, $minLargest, $maxSmallest, $maxLargest);
    }

    public function isFixed() : bool
    {
        return $this->minSmallest === $this->minLargest && $this->maxSmallest === $this->maxLargest;
    }

    public function getMinimum() : int
    {
        return $this->minSmallest;
    }

    public function getLargestMinimum() : int
    {
        return $this->minLargest;
    }

    public function getSmallestMaximum() : int
    {
        return $this->maxSmallest;
    }

    public function getMaximum() : int
    {
        return $this->maxLargest;
    }

    public function isValidValue(int $value) : bool
    {
        return ($value >= $this->getMinimum() && $value <= $this->getMaximum());
    }

    public function checkValidValue(int $value, TemporalFieldInterface $field) : int
    {
        if (!$this->isValidValue($value)) {
            throw new DateTimeException('Invalid value for ' . $field . ' (values values: ' . $this . '): ' . $value);
        }

        return $value;
    }

    public function __toString() : string
    {
        $result = (string) $this->minSmallest;

        if ($this->minSmallest !== $this->minLargest) {
            $result .= '/' . $this->minLargest;
        }

        $result .= ' - ' . $this->maxSmallest;

        if ($this->maxSmallest !== $this->maxLargest) {
            $result .= '/' . $this->maxLargest;
        }

        return $result;
    }
}
