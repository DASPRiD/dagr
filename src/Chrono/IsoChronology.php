<?php
declare(strict_types = 1);

namespace Dagr\Chrono;

final class IsoChronology extends AbstractChronology
{
    /**
     * @var self
     */
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance() : self
    {
        return self::$instance ?: self::$instance = new self();
    }

    public function getId() : string
    {
        return 'ISO';
    }

    public function getCalendarType() : string
    {
        return 'iso8601';
    }

    public function isLeapYear(int $prolepticYear) : bool
    {
        return (0 === ($prolepticYear & 3) && ((0 !== $prolepticYear % 100) || (0 === $prolepticYear % 400)));
    }
}
