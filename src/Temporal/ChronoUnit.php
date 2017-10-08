<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

use Dagr\Duration;
use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;

final class ChronoUnit implements TemporalUnitInterface
{
    use NotClonableTrait;
    use NotSerializableTrait;

    private const TIME_BASED = 'time-based';
    private const DATE_BASED = 'date-based';

    /**
     * @var string
     */
    private $name;

    /**
     * Duration
     */
    private $duration;

    /**
     * @var string
     */
    private $type;

    /**
     * @var self[]
     */
    private static $cache;

    private function __construct(string $name, Duration $estimatedDuration, string $type)
    {
        $this->name = $name;
        $this->duration = $estimatedDuration;
        $this->type = $type;
    }

    public static function nanos() : self
    {
        return self::$cache['nanos'] ?? self::$cache['nanos'] = new self(
            'Nanos',
            Duration::ofNanos(1),
            self::TIME_BASED
        );
    }

    public static function micros() : self
    {
        return self::$cache['micros'] ?? self::$cache['micros'] = new self(
            'Micros',
            Duration::ofNanos(1000),
            self::TIME_BASED
        );
    }

    public static function millis() : self
    {
        return self::$cache['millis'] ?? self::$cache['millis'] = new self(
            'Millis',
            Duration::ofMillis(1),
            self::TIME_BASED
        );
    }

    public static function seconds() : self
    {
        return self::$cache['seconds'] ?? self::$cache['seconds'] = new self(
            'Seconds',
            Duration::ofSeconds(1),
            self::TIME_BASED
        );
    }

    public static function minutes() : self
    {
        return self::$cache['minutes'] ?? self::$cache['minutes'] = new self(
            'Minutes',
            Duration::ofSeconds(60),
            self::TIME_BASED
        );
    }

    public static function hours() : self
    {
        return self::$cache['hours'] ?? self::$cache['hours'] = new self(
            'Hours',
            Duration::ofSeconds(3600),
            self::TIME_BASED
        );
    }

    public static function halfDays() : self
    {
        return self::$cache['halfDays'] ?? self::$cache['halfDays'] = new self(
            'HalfDays',
            Duration::ofSeconds(43200),
            self::DATE_BASED
        );
    }

    public static function days() : self
    {
        return self::$cache['days'] ?? self::$cache['days'] = new self(
            'Days',
            Duration::ofSeconds(86400),
            self::DATE_BASED
        );
    }

    public static function weeks() : self
    {
        return self::$cache['weeks'] ?? self::$cache['weeks'] = new self(
            'Weeks',
            Duration::ofSeconds(7 * 86400),
            self::DATE_BASED
        );
    }

    public static function months() : self
    {
        return self::$cache['months'] ?? self::$cache['months'] = new self(
            'Months',
            Duration::ofSeconds(intdiv(31556952, 12)),
            self::DATE_BASED
        );
    }

    public static function years() : self
    {
        return self::$cache['years'] ?? self::$cache['years'] = new self(
            'Years',
            Duration::ofSeconds(31556952),
            self::DATE_BASED
        );
    }

    public static function decades() : self
    {
        return self::$cache['decades'] ?? self::$cache['decades'] = new self(
            'Decades',
            Duration::ofSeconds(31556952 * 10),
            self::DATE_BASED
        );
    }

    public static function centuries() : self
    {
        return self::$cache['centuries'] ?? self::$cache['centuries'] = new self(
            'Centuries',
            Duration::ofSeconds(31556952 * 100),
            self::DATE_BASED
        );
    }

    public static function millennia() : self
    {
        return self::$cache['millennia'] ?? self::$cache['millennia'] = new self(
            'Millennia',
            Duration::ofSeconds(31556952 * 1000),
            self::DATE_BASED
        );
    }

    public static function eras() : self
    {
        return self::$cache['eras'] ?? self::$cache['eras'] = new self(
            'Eras',
            Duration::ofSeconds(31556952 * 1000000000),
            self::DATE_BASED
        );
    }

    public static function forever() : self
    {
        return self::$cache['forever'] ?? self::$cache['forever'] = new self(
            'Forever',
            Duration::ofSeconds(PHP_INT_MAX),
            self::DATE_BASED
        );
    }

    public function getDuration() : Duration
    {
        return $this->duration;
    }

    public function isDurationEstimated() : bool
    {
        return $this->type === self::DATE_BASED;
    }

    public function isDateBased() : bool
    {
        return $this->type === self::DATE_BASED && $this !== self::forever();
    }

    public function isTimeBased() : bool
    {
        return $this->type === self::TIME_BASED;
    }

    public function isSupportedBy(TemporalInterface $temporal) : bool
    {
        return $temporal->isSupportedUnit($this);
    }

    public function addTo(TemporalInterface $temporal, int $amount) : TemporalInterface
    {
        return $temporal->plus($amount, $this);
    }

    public function between(TemporalInterface $temporal1Inclusive, TemporalInterface $temporal2Exclusive) : int
    {
        return $temporal1Inclusive->until($temporal2Exclusive, $this);
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
