<?php
declare(strict_types = 1);

namespace Dagr\TimeZone;

use Dagr\LocalDate;
use Dagr\LocalTime;

final class TimeZoneOffsetTransitionRule
{
    /**
     * @var Month
     */
    private $month;

    /**
     * @var int
     */
    private $dayOfMonth;

    /**
     * @var int
     */
    private $dayOfWeek;

    /**
     * @var LocalTime
     */
    private $time;

    /**
     * @var bool
     */
    private $timeEndOfDay;

    /**
     * @var TimeDefinition
     */
    private $timeDefinition;

    /**
     * @var TimeZoneOffset
     */
    private $standardOffset;

    /**
     * @var TimeZoneOffset
     */
    private $offsetBefore;

    /**
     * @var TimeZoneOffset
     */
    private $offsetAfter;

    public function createTransition(int $year)
    {
        if ($this->dayOfMonth < 0) {
            $date = LocalDate::of($year, $this->month, 1);
        }
    }
}
