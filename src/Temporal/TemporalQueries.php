<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

use Dagr\LocalDate;
use Dagr\LocalTime;
use Dagr\TimeZone\AbstractTimeZone;
use Dagr\TimeZone\AbstractTimeZoneId;
use Dagr\TimeZone\TimeZoneId;
use Dagr\TimeZone\TimeZoneOffset;

final class TemporalQueries
{
    /**
     * @var TemporalQueryInterface[]
     */
    private static $cache = [];

    private function __construct()
    {
    }

    public static function zoneId() : TemporalQueryInterface
    {
        return self::$cache['zoneId'] ?? self::$cache['zoneId'] = new class implements
            TemporalQueryInterface
        {
            public function queryFrom(TemporalAccessorInterface $temporal) : ?TimeZoneId
            {
                return $temporal->query(TemporalQueries::zoneId());
            }
        };
    }

    public static function chronology() : TemporalQueryInterface
    {
        return self::$cache['chronology'] ?? self::$cache['chronology'] = new class implements
            TemporalQueryInterface
        {
            public function queryFrom(TemporalAccessorInterface $temporal) : ?TemporalAccessorInterface
            {
                return $temporal->query(TemporalQueries::chronology());
            }
        };
    }

    public static function precision() : TemporalQueryInterface
    {
        return self::$cache['precision'] ?? self::$cache['precision'] = new class implements
            TemporalQueryInterface
        {
            public function queryFrom(TemporalAccessorInterface $temporal) : ?ChronoUnit
            {
                return $temporal->query(TemporalQueries::precision());
            }
        };
    }

    public static function offset() : TemporalQueryInterface
    {
        return self::$cache['offset'] ?? self::$cache['offset'] = new class implements
            TemporalQueryInterface
        {
            public function queryFrom(TemporalAccessorInterface $temporal) : ?TimeZoneOffset
            {
                if ($temporal->isSupportedField(ChronoField::offsetSeconds())) {
                    return TimeZoneOffset::ofTotalSeconds($temporal->get(ChronoField::offsetSeconds()));
                }

                return null;
            }
        };
    }

    public static function zone() : TemporalQueryInterface
    {
        return self::$cache['zone'] ?? self::$cache['zone'] = new class implements
            TemporalQueryInterface
        {
            public function queryFrom(TemporalAccessorInterface $temporal) : ?AbstractTimeZone
            {
                $zone = $temporal->query(TemporalQueries::zoneId());
                return (null !== $zone ? $zone : $temporal->query(TemporalQueries::offset()));
            }
        };
    }

    public static function localDate() : TemporalQueryInterface
    {
        return self::$cache['localDate'] ?? self::$cache['localDate'] = new class implements
            TemporalQueryInterface
        {
            public function queryFrom(TemporalAccessorInterface $temporal) : ?LocalDate
            {
                if ($temporal->isSupportedField(ChronoField::epochDay())) {
                    return LocalDate::ofEpochDay($temporal->get(ChronoField::epochDay()));
                }

                return null;
            }
        };
    }

    public static function localTime() : TemporalQueryInterface
    {
        return self::$cache['localTime'] ?? self::$cache['localTime'] = new class implements
            TemporalQueryInterface
        {
            public function queryFrom(TemporalAccessorInterface $temporal) : ?LocalTime
            {
                if ($temporal->isSupportedField(ChronoField::nanoOfDay())) {
                    return LocalTime::ofNanoOfDay($temporal->get(ChronoField::nanoOfDay()));
                }

                return null;
            }
        };
    }
}
