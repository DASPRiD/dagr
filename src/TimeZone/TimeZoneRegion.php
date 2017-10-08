<?php
declare(strict_types = 1);

namespace Dagr\TimeZone;

use Dagr\Tzdb\Provider;

final class TimeZoneRegion extends AbstractTimeZone
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var TimeZoneRules
     */
    private $rules;

    /**
     * @var self[]
     */
    private static $cache = [];

    private function __construct(string $id, TimeZoneRules $rules)
    {
        $this->id = $id;
        $this->rules = $rules;
    }

    public static function ofId(string $timeZoneId) : self
    {
        $cacheKey = strtolower($timeZoneId);

        if (array_key_exists($cacheKey, self::$cache)) {
            return self::$cache[$cacheKey];
        }

        $zoneInfo = Provider::provideZone($timeZoneId);

        if (null === $zoneInfo) {
            // @todo exception
        }

        $rules = new TimeZoneRules($offset);

        return (self::$cache[$cacheKey] = new self(
            $timeZoneId,
            $rules
        ));
    }
}
