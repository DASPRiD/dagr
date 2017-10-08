<?php
declare(strict_types = 1);

namespace Dagr\TimeZone;

use Dagr\LocalTime;

final class TimeZoneOffset extends AbstractTimeZone
{
    private const MAX_SECONDS = 18 * LocalTime::SECONDS_PER_HOUR;

    /**
     * @var int
     */
    private $totalSeconds;

    /**
     * @var string
     */
    private $id;

    /**
     * @var self[]
     */
    private static $cache;

    public function __construct(int $totalSeconds)
    {
        $this->totalSeconds = $totalSeconds;
        $this->id = self::buildId($totalSeconds);
    }

    public static function utc()
    {
        return self::ofTotalSeconds(0);
    }

    public static function ofHours(int $hours) : self
    {
        return self::ofHoursMinutesAndSeconds($hours, 0, 0);
    }

    public static function ofHoursAndMinutes(int $hours, int $minutes) : self
    {
        return self::ofHoursMinutesAndSeconds($hours, $minutes, 0);
    }

    public static function ofHoursMinutesAndSeconds(int $hours, int $minutes, int $seconds) : self
    {
        return self::ofTotalSeconds(
            $hours * LocalTime::SECONDS_PER_HOUR
            + $minutes * LocalTime::SECONDS_PER_MINUTE
            + $seconds
        );
    }

    public static function ofTotalSeconds(int $totalSeconds) : self
    {
        if (abs($totalSeconds) > self::MAX_SECONDS) {
            // @todo throw eception
        }

        if (0 !== $totalSeconds % (15 * LocalTime::SECONDS_PER_MINUTE)) {
            return new self($totalSeconds);
        }

        if (array_key_exists($totalSeconds, self::$cache)) {
            return self::$cache[$totalSeconds];
        }

        return (self::$cache[$totalSeconds] = new self($totalSeconds));
    }

    private static function calculateTotalSeconds(int $hours, int $minutes, int $seconds) : int
    {
        return $hours * LocalTime::SECONDS_PER_HOUR + $minutes * LocalTime::SECONDS_PER_MINUTE + $seconds;
    }

    private static function buildId(int $totalSeconds) : string
    {
        if (0 === $totalSeconds) {
            return 'Z';
        }

        $absTotalSeconds = abs($totalSeconds);
        $absHours = $absTotalSeconds / LocalTime::SECONDS_PER_DAY;
        $absMinutes = ((int) ($absTotalSeconds / LocalTime::SECONDS_PER_MINUTE)) % LocalTime::MINUTES_PER_HOUR;

        $result = sprintf('%s%02d:%02d', $totalSeconds < 0 ? '-' : '+', $absHours, $absMinutes);
        $absSeconds = $absTotalSeconds % LocalTime::SECONDS_PER_MINUTE;

        if ($absSeconds > 0) {
            $result .= sprintf(':%02d', $absSeconds);
        }

        return $result;
    }

    public function getTotalSeconds() : int
    {
        return $this->totalSeconds;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getRules() : TimeZoneRules
    {
        return TimeZoneRules::ofOffset($this);
    }
}
