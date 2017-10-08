<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

use Dagr\Year;

final class ChronoField implements TemporalFieldInterface
{
    private const TIME_BASED = 'time-based';
    private const DATE_BASED = 'date-based';
    private const OTHER = 'other';

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
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $displayNameKey;

    /**
     * @var self[]
     */
    private static $cache;

    private function __construct(
        string $name,
        TemporalUnitInterface $baseUnit,
        TemporalUnitInterface $rangeUnit,
        ValueRange $range,
        string $type,
        ?string $displayNameKey = null
    ) {
        $this->name = $name;
        $this->baseUnit = $baseUnit;
        $this->rangeUnit = $rangeUnit;
        $this->range = $range;
        $this->type = $type;
        $this->displayNameKey = $displayNameKey;
    }

    public static function nanoOfSecond() : self
    {
        return self::$cache['nanoOfSecond'] ?? self::$cache['nanoOfSecond'] = new self(
            'NanoOfSecond',
            ChronoUnit::nanos(),
            ChronoUnit::seconds(),
            ValueRange::ofTwo(0, 999999999),
            self::TIME_BASED
        );
    }

    public static function nanoOfDay() : self
    {
        return self::$cache['nanoOfDay'] ?? self::$cache['nanoOfDay'] = new self(
            'NanoOfDay',
            ChronoUnit::nanos(),
            ChronoUnit::days(),
            ValueRange::ofTwo(0, 86400 * 1000000000 - 1),
            self::TIME_BASED
        );
    }

    public static function microOfSecond() : self
    {
        return self::$cache['microOfSecond'] ?? self::$cache['microOfSecond'] = new self(
            'MicroOfSecond',
            ChronoUnit::micros(),
            ChronoUnit::seconds(),
            ValueRange::ofTwo(0, 999999),
            self::TIME_BASED
        );
    }

    public static function microOfDay() : self
    {
        return self::$cache['microOfDay'] ?? self::$cache['microOfDay'] = new self(
            'MicroOfDay',
            ChronoUnit::micros(),
            ChronoUnit::days(),
            ValueRange::ofTwo(0, 86400 * 1000000 - 1),
            self::TIME_BASED
        );
    }

    public static function milliOfSecond() : self
    {
        return self::$cache['milliOfSecond'] ?? self::$cache['milliOfSecond'] = new self(
            'MilliOfSecond',
            ChronoUnit::millis(),
            ChronoUnit::seconds(),
            ValueRange::ofTwo(0, 999),
            self::TIME_BASED
        );
    }

    public static function milliOfDay() : self
    {
        return self::$cache['milliOfDay'] ?? self::$cache['milliOfDay'] = new self(
            'MilliOfDay',
            ChronoUnit::millis(),
            ChronoUnit::days(),
            ValueRange::ofTwo(0, 86400 * 1000 - 1),
            self::TIME_BASED
        );
    }

    public static function secondOfMinute() : self
    {
        return self::$cache['secondOfMinute'] ?? self::$cache['secondOfMinute'] = new self(
            'SecondOfMinute',
            ChronoUnit::seconds(),
            ChronoUnit::minutes(),
            ValueRange::ofTwo(0, 59),
            self::TIME_BASED,
            'second'
        );
    }

    public static function secondOfDay() : self
    {
        return self::$cache['secondOfDay'] ?? self::$cache['secondOfDay'] = new self(
            'SecondOfDay',
            ChronoUnit::seconds(),
            ChronoUnit::days(),
            ValueRange::ofTwo(0, 86400 - 1),
            self::TIME_BASED
        );
    }

    public static function minuteOfHour() : self
    {
        return self::$cache['minuteOfHour'] ?? self::$cache['minuteOfHour'] = new self(
            'MinuteOfHour',
            ChronoUnit::minutes(),
            ChronoUnit::hours(),
            ValueRange::ofTwo(0, 59),
            self::TIME_BASED,
            'minute'
        );
    }

    public static function minuteOfDay() : self
    {
        return self::$cache['minuteOfDay'] ?? self::$cache['minuteOfDay'] = new self(
            'MinuteOfDay',
            ChronoUnit::minutes(),
            ChronoUnit::days(),
            ValueRange::ofTwo(0, 24 * 60 - 1),
            self::TIME_BASED
        );
    }

    public static function hourOfAmPm() : self
    {
        return self::$cache['hourOfAmPm'] ?? self::$cache['hourOfAmPm'] = new self(
            'HourOfAmPm',
            ChronoUnit::hours(),
            ChronoUnit::halfDays(),
            ValueRange::ofTwo(0, 11),
            self::TIME_BASED
        );
    }

    public static function clockHourOfAmPm() : self
    {
        return self::$cache['clockHourOfAmPm'] ?? self::$cache['clockHourOfAmPm'] = new self(
            'ClockHourOfAmPm',
            ChronoUnit::hours(),
            ChronoUnit::halfDays(),
            ValueRange::ofTwo(1, 12),
            self::TIME_BASED
        );
    }

    public static function hourOfDay() : self
    {
        return self::$cache['hourOfDay'] ?? self::$cache['hourOfDay'] = new self(
            'HourOfDay',
            ChronoUnit::hours(),
            ChronoUnit::days(),
            ValueRange::ofTwo(0, 23),
            self::TIME_BASED
        );
    }

    public static function clockHourOfDay() : self
    {
        return self::$cache['clockHoursOfDay'] ?? self::$cache['clockHourOfDay'] = new self(
            'ClockHourOfDay',
            ChronoUnit::hours(),
            ChronoUnit::halfDays(),
            ValueRange::ofTwo(1, 24),
            self::TIME_BASED
        );
    }

    public static function amPmOfDay() : self
    {
        return self::$cache['amPmOfDay'] ?? self::$cache['amPmOfDay'] = new self(
            'AmPmOfDay',
            ChronoUnit::halfDays(),
            ChronoUnit::days(),
            ValueRange::ofTwo(0, 1),
            self::TIME_BASED
        );
    }

    public static function dayOfWeek() : self
    {
        return self::$cache['dayOfWeek'] ?? self::$cache['dayOfWeek'] = new self(
            'DayOfWeek',
            ChronoUnit::days(),
            ChronoUnit::weeks(),
            ValueRange::ofTwo(1, 7),
            self::DATE_BASED
        );
    }

    public static function alignedDayOfWeekInYear() : self
    {
        return self::$cache['alignedDayOfWeekInYear'] ?? self::$cache['alignedDayOfWeekInYear'] = new self(
            'AlignedDayOfWeekInYear',
            ChronoUnit::days(),
            ChronoUnit::weeks(),
            ValueRange::ofTwo(1, 7),
            self::DATE_BASED
        );
    }

    public static function dayOfMonth() : self
    {
        return self::$cache['dayOfMonth'] ?? self::$cache['dayOfMonth'] = new self(
            'DayOfMonth',
            ChronoUnit::days(),
            ChronoUnit::months(),
            ValueRange::ofThree(1, 28, 31),
            self::DATE_BASED,
            'day'
        );
    }

    public static function dayOfYear() : self
    {
        return self::$cache['dayOfYear'] ?? self::$cache['dayOfYear'] = new self(
            'DayOfYear',
            ChronoUnit::days(),
            ChronoUnit::years(),
            ValueRange::ofThree(1, 365, 366),
            self::DATE_BASED,
            'day'
        );
    }

    public static function epochDay() : self
    {
        return self::$cache['epochDay'] ?? self::$cache['epochDay'] = new self(
            'EpochDay',
            ChronoUnit::days(),
            ChronoUnit::forever(),
            ValueRange::ofTwo(1, 365, 366), // @todo
            self::DATE_BASED
        );
    }

    public static function alignedWeekOfMonth() : self
    {
        return self::$cache['alignedWeekOfMonth'] ?? self::$cache['alignedWeekOfMonth'] = new self(
            'AlignedWeekOfMonth',
            ChronoUnit::weeks(),
            ChronoUnit::months(),
            ValueRange::ofThree(1, 4, 5),
            self::DATE_BASED
        );
    }

    public static function alignedWeekOfYear() : self
    {
        return self::$cache['alignedWeekOfYear'] ?? self::$cache['alignedWeekOfYear'] = new self(
            'AlignedWeekOfYear',
            ChronoUnit::weeks(),
            ChronoUnit::years(),
            ValueRange::ofTwo(1, 53),
            self::DATE_BASED
        );
    }

    public static function monthOfYear() : self
    {
        return self::$cache['monthOfYear'] ?? self::$cache['monthOfYear'] = new self(
            'MonthOfYear',
            ChronoUnit::months(),
            ChronoUnit::years(),
            ValueRange::ofTwo(1, 12),
            self::DATE_BASED,
            'month'
        );
    }

    public static function prolepticMonth() : self
    {
        return self::$cache['prolepticMonth'] ?? self::$cache['prolepticMonth'] = new self(
            'ProlepticMonth',
            ChronoUnit::months(),
            ChronoUnit::forever(),
            ValueRange::ofTwo(Year::MIN_VALUE * 12, YEAR::MAX_VALUE * 12 + 11),
            self::DATE_BASED
        );
    }

    public static function yearOfEra() : self
    {
        return self::$cache['yearOfEra'] ?? self::$cache['yearOfEra'] = new self(
            'YearOfEra',
            ChronoUnit::years(),
            ChronoUnit::forever(),
            ValueRange::ofThree(1, YEAR::MAX_VALUE, YEAR::MAX_VALUE + 1),
            self::DATE_BASED
        );
    }

    public static function year() : self
    {
        return self::$cache['year'] ?? self::$cache['year'] = new self(
            'Year',
            ChronoUnit::years(),
            ChronoUnit::forever(),
            ValueRange::ofTwo(Year::MIN_VALUE, Year::MAX_VALUE),
            self::DATE_BASED,
            'year'
        );
    }

    public static function era() : self
    {
        return self::$cache['era'] ?? self::$cache['era'] = new self(
            'Era',
            ChronoUnit::eras(),
            ChronoUnit::forever(),
            ValueRange::ofTwo(0, 1),
            self::DATE_BASED,
            'era'
        );
    }

    public static function instantSeconds() : self
    {
        return self::$cache['instantSeconds'] ?? self::$cache['instantSeconds'] = new self(
            'InstantSeconds',
            ChronoUnit::seconds(),
            ChronoUnit::forever(),
            ValueRange::ofTwo(PHP_INT_MIN, PHP_INT_MAX),
            self::OTHER
        );
    }

    public static function offsetSeconds() : self
    {
        return self::$cache['offsetSeconds'] ?? self::$cache['offsetSeconds'] = new self(
            'OffsetSeconds',
            ChronoUnit::seconds(),
            ChronoUnit::forever(),
            ValueRange::ofTwo(-18 * 3600, 18 * 3600),
            self::OTHER
        );
    }

    /**
     * @return self[]
     */
    public static function values() : array
    {
        return [
            self::nanoOfSecond(),
            self::nanoOfDay(),
            self::microOfSecond(),
            self::microOfDay(),
            self::milliOfSecond(),
            self::milliOfDay(),
            self::secondOfMinute(),
            self::secondOfDay(),
            self::minuteOfHour(),
            self::minuteOfDay(),
            self::hourOfAmPm(),
            self::clockHourOfAmPm(),
            self::hourOfDay(),
            self::clockHourOfDay(),
            self::amPmOfDay(),
            self::dayOfWeek(),
            self::alignedDayOfWeekInYear(),
            self::dayOfMonth(),
            self::dayOfYear(),
            self::epochDay(),
            self::alignedWeekOfMonth(),
            self::alignedWeekOfYear(),
            self::monthOfYear(),
            self::prolepticMonth(),
            self::yearOfEra(),
            self::year(),
            self::era(),
            self::instantSeconds(),
            self::offsetSeconds(),
        ];
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
        return self::DATE_BASED === $this->type;
    }

    public function isTimeBased() : bool
    {
        return self::TIME_BASED === $this->type;
    }

    public function checkValidValue(int $value) : int
    {
        return $this->range->checkValidValue($value, $this);
    }

    public function isSupportedBy(TemporalAccessorInterface $temporal) : bool
    {
        return $temporal->isSupportedField($this);
    }

    public function rangeRefinedBy(TemporalAccessorInterface $temporal) : ValueRange
    {
        return $temporal->range($this);
    }

    public function getFrom(TemporalAccessorInterface $temporal) : int
    {
        return $temporal->getInt($this);
    }

    public function adjustInto(TemporalInterface $temporal, int $newValue) : TemporalInterface
    {
        return $temporal->withField($this, $newValue);
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
