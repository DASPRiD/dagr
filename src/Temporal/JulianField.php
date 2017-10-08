<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

use Dagr\Exception\DateTimeException;

final class JulianField implements TemporalFieldInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var TemporalUnitInterface
     */
    private $baseUnit;

    /**
     * @var TemporalUnitInterface
     */
    private $rangeUnit;

    /**
     * @var ValueRange
     */
    private $range;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var self[]
     */
    private static $cache;

    private function __construct(
        string $name,
        TemporalUnitInterface $baseUnit,
        TemporalUnitInterface $rangeUnit,
        int $offset
    ) {
        $this->name = $name;
        $this->baseUnit = $baseUnit;
        $this->rangeUnit = $rangeUnit;
        $this->range = ValueRange::ofTwo(-365243219162 + $offset, 365241780471 + $offset);
        $this->offset = $offset;
    }

    public static function julianDay() : self
    {
        return self::$cache['julianDay'] ?? self::$cache['julianDay'] = new self(
            'JulianDay',
            ChronoUnit::days(),
            ChronoUnit::forever(),
                2440588
        );
    }

    public static function modifiedJulianDay() : self
    {
        return self::$cache['modifiedJulianDay'] ?? self::$cache['modifiedJulianDay'] = new self(
            'ModifiedJulianDay',
            ChronoUnit::days(),
            ChronoUnit::forever(),
            40587
        );
    }

    public static function rataDie() : self
    {
        return self::$cache['rataDie'] ?? self::$cache['rataDie'] = new self(
            'RataDie',
            ChronoUnit::days(),
            ChronoUnit::forever(),
            719163
        );
    }

    public function getBaseUnit() : TemporalUnitInterface
    {
        return $this->baseUnit;
    }

    public function getRangeUnit() : TemporalUnitInterface
    {
        return $this->rangeUnit;
    }

    public function range() : ValueRange
    {
        return $this->range;
    }

    public function isDateBased() : bool
    {
        return true;
    }

    public function isTimeBased() : bool
    {
        return false;
    }

    public function isSupportedBy(TemporalAccessorInterface $temporal) : bool
    {
        return $temporal->isSupportedField(ChronoField::epochDay());
    }

    public function rangeRefinedBy(TemporalAccessorInterface $temporal) : ValueRange
    {
        if (!$this->isSupportedBy($temporal)) {
            throw new DateTimeException('Unsupported field: ' . $this);
        }

        return $this->range();
    }

    public function getFrom(TemporalAccessorInterface $temporal) : int
    {
        return $temporal->getInt(ChronoField::epochDay()) + $this->offset;
    }

    public function adjustInto(TemporalInterface $temporal, int $newValue) : TemporalInterface
    {
        if (!$this->range->isValidValue($newValue)) {
            throw new DateTimeException('Invalid value: ' . $this->name . ' ' . $newValue);
        }

        return $temporal->withField(ChronoField::epochDay(), Math::subtractExact($newValue, $this->offset));
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
