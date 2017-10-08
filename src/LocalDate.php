<?php
declare(strict_types = 1);

namespace Dagr;

use Dagr\Temporal\ChronoField;
use Dagr\TimeZone\AbstractTimeZone;

final class LocalDate
{
    private const DAYS_PER_CYCLE = 146097;
    private const DAYS_0000_TO_1970 = (self::DAYS_PER_CYCLE * 5) - (30 * 365 * 7);

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $month;

    /**
     * @var int
     */
    private $day;

    private function __construct(int $year, int $month, int $day)
    {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    public static function now() : self
    {
        return self::nowWith(SystemClock::defaultTimeZone());
    }

    public static function nowAt(AbstractTimeZone $timeZoneId) : self
    {

    }

    public static function nowWith(ClockInterface $clock) : self
    {

    }

    public static function ofEpochDay(int $epochDay) : self
    {
        $zeroDay = $epochDay + self::DAYS_0000_TO_1970;
        // Find the march-based year.
        // Adjust to 0000-03-01 so leap day is at end of four year cycle.
        $zeroDay -= 60;
        $adjust = 0;

        if ($zeroDay < 0) {
            // adjust negative years to positive for calculation
            $adjustCycles = intdiv($zeroDay + 1, self::DAYS_PER_CYCLE) - 1;
            $adjust = $adjustCycles * 400;
            $zeroDay += -$adjustCycles * self::DAYS_PER_CYCLE;
        }

        $yearEstimate = intdiv(400 * $zeroDay + 591, self::DAYS_PER_CYCLE);
        $dayEstimate = $zeroDay - (
            365 * $yearEstimate + intdiv($yearEstimate, 4) - intdiv($yearEstimate, 100) + intdiv($yearEstimate, 400)
        );

        if ($dayEstimate < 0) {
            // fix estimate
            --$yearEstimate;
            $dayEstimate = $zeroDay - (
                365 * $yearEstimate + intdiv($yearEstimate, 4) - intdiv($yearEstimate, 100) + intdiv($yearEstimate, 400)
            );
        }

        // Reset any negative year.
        $yearEstimate += $adjust;
        $marchDayOfYear0 = $dayEstimate;

        // Convert march-based values back to january-based.
        $marchMonth0 = intdiv($marchDayOfYear0 * 5 + 2, 153);
        $month = ($marchMonth0 + 2) % 12 + 1;
        $dayOfMonth = $marchDayOfYear0 - intdiv($marchMonth0 * 306 + 5, 10) + 1;
        $yearEstimate += intdiv($marchMonth0, 10);

        // Check year now we are certain it is correct.
        $year = ChronoField::year()->checkValidValue($yearEstimate);
        return new LocalDate($year, $month, $dayOfMonth);
    }

    private static function create(int $year, int $month, int $dayOfMonth)
    {
        if ($dayOfMonth <= 28) {
            return new LocalDate($year, $month, $dayOfMonth);
        }

        $maxDayOfMonth = 31;

        switch ($month) {
            case 2:
                break;

            case 4:
            case 6:
            case 9:
            case 11:
                $maxDayOfMonth = 30;
        }

        if ($dayOfMonth <= $maxDayOfMonth) {
            return new LocalDate($year, $month, $dayOfMonth);
        }

        if (29 === $dayOfMonth) {
            // @todo throw exception: Invalid date 'February 29' as 'YEAR' is not a leap year
        }

        // @todo throw exception: Invalid date 'MONTHNAME DOM'
    }
}
