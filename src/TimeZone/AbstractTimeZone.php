<?php
declare(strict_types = 1);

namespace Dagr\TimeZone;

abstract class AbstractTimeZone
{
    /**
     * @var self
     */
    private static $systemDefault;

    public static function systemDefault() : self
    {
        return self::$systemDefault ?: self::$systemDefault = TimeZoneRegion::ofId(date_default_timezone_get());
    }

    abstract public function getId() : string;

    abstract public function getRules() : TimeZoneRules;

    public function equals(AbstractTimeZone $other) : bool
    {
        return $this === $other || $this->getId() === $other->getId();
    }
}
