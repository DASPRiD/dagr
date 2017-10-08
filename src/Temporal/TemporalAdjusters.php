<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

use Dagr\DayOfWeek;

final class TemporalAdjusters
{
    /**
     * @var TemporalAdjusterInterface[]
     */
    private static $cache = [];

    private function __construct()
    {
    }

    public static function firstDayOfMonth() : TemporalAdjusterInterface
    {
        return self::$cache['firstDayOfMonth'] ?? self::$cache['firstDayOfMonth'] = new class implements
            TemporalAdjusterInterface
        {
            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                return $temporal->withField(ChronoField::dayOfMonth(), 1);
            }
        };
    }

    public static function lastDayOfMonth() : TemporalAdjusterInterface
    {
        return self::$cache['lastDayOfMonth'] ?? self::$cache['lastDayOfMonth'] = new class implements
            TemporalAdjusterInterface
        {
            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                return $temporal->withField(
                    ChronoField::dayOfMonth(),
                    $temporal->range(ChronoField::dayOfMonth())->getMaximum()
                );
            }
        };
    }

    public static function firstDayOfNextMonth() : TemporalAdjusterInterface
    {
        return self::$cache['firstDayOfNextMonth'] ?? self::$cache['firstDayOfNextMonth'] = new class implements
            TemporalAdjusterInterface
        {
            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                return $temporal->withField(ChronoField::dayOfMonth(), 1)->plus(1, ChronoUnit::months());
            }
        };
    }

    public static function firstDayOfYear() : TemporalAdjusterInterface
    {
        return self::$cache['firstDayOfYear'] ?? self::$cache['firstDayOfYear'] = new class implements
            TemporalAdjusterInterface
        {
            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                return $temporal->withField(ChronoField::dayOfYear(), 1);
            }
        };
    }

    public static function lastDayOfYear() : TemporalAdjusterInterface
    {
        return self::$cache['lastDayOfYear'] ?? self::$cache['lastDayOfYear'] = new class implements
            TemporalAdjusterInterface
        {
            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                return $temporal->withField(
                    ChronoField::dayOfYear(),
                    $temporal->range(ChronoField::dayOfYear())->getMaximum()
                );
            }
        };
    }

    public static function firstDayOfNextYear() : TemporalAdjusterInterface
    {
        return self::$cache['firstDayOfNextYear'] ?? self::$cache['firstDayOfNextYear'] = new class implements
            TemporalAdjusterInterface
        {
            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                return $temporal->withField(ChronoField::dayOfYear(), 1)->plus(1, ChronoUnit::years());
            }
        };
    }

    public static function firstInMonth(DayOfWeek $dayOfWeek) : TemporalAdjusterInterface
    {
        return self::$cache['firstInMonth'] ?? self::$cache['firstInMonth'] = self::dayOfWeekInMonth(1, $dayOfWeek);
    }

    public static function lastInMonth(DayOfWeek $dayOfWeek) : TemporalAdjusterInterface
    {
        return self::$cache['lastInMonth'] ?? self::$cache['lastInMonth'] = self::dayOfWeekInMonth(-1, $dayOfWeek);
    }

    public static function dayOfWeekInMonth(int $ordinal, DayOfWeek $dayOfWeek) : TemporalAdjusterInterface
    {
        $cacheKey = 'dayOfWeekInMonth' . $ordinal;

        if ($ordinal >= 0) {
            return self::$cache[$cacheKey] ?? self::$cache[$cacheKey] = new class($dayOfWeek->getValue()) implements
                TemporalAdjusterInterface
            {
                /**
                 * @var int
                 */
                private $value;

                /**
                 * @var int
                 */
                private $ordinal;

                public function __construct(int $value, int $ordinal)
                {
                    $this->value = $value;
                    $this->ordinal = $ordinal;
                }

                public function adjustInto(TemporalInterface $temporal) : TemporalInterface
                {
                    $temp = $temporal->withField(ChronoField::dayOfMonth(), 1);
                    $currentDayOfMonth = $temp->get(ChronoField::dayOfWeek());
                    $diff = ($this->value - $currentDayOfMonth + 7) % 7;
                    $diff += ($this->ordinal - 1) * 7;
                    return $temp->plus($diff, ChronoUnit::days());
                }
            };
        }

        return self::$cache[$cacheKey] ?? self::$cache[$cacheKey] = new class($dayOfWeek->getValue()) implements
            TemporalAdjusterInterface
        {
            /**
             * @var int
             */
            private $value;

            /**
             * @var int
             */
            private $ordinal;

            public function __construct(int $value, int $ordinal)
            {
                $this->value = $value;
                $this->ordinal = $ordinal;
            }

            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                $temp = $temporal->withField(
                    ChronoField::dayOfMonth(),
                    $temporal->range(ChronoField::dayOfMonth())->getMaximum()
                );
                $currentDayOfMonth = $temp->get(ChronoField::dayOfWeek());
                $diff = $this->value - $currentDayOfMonth;
                $diff += (-$this->ordinal - 1) * 7;
                return $temp->plus($diff, ChronoUnit::days());
            }
        };
    }

    public static function next(DayOfWeek $dayOfWeek) : TemporalAdjusterInterface
    {
        return self::$cache['next'] ?? self::$cache['next'] = new class($dayOfWeek->getValue()) implements
            TemporalAdjusterInterface
        {
            /**
             * @var int
             */
            private $value;

            public function __construct(int $value)
            {
                $this->value = $value;
            }

            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                $dayOfWeek = $temporal->get(ChronoField::dayOfWeek());
                $daysDiff = $dayOfWeek - $this->value;
                return $temporal->plus($daysDiff >= 0 ? 7 - $daysDiff : -$daysDiff, ChronoUnit::days());
            }
        };
    }

    public static function nextOrSame(DayOfWeek $dayOfWeek) : TemporalAdjusterInterface
    {
        return self::$cache['nextOrSame'] ?? self::$cache['nextOrSame'] = new class($dayOfWeek->getValue()) implements
            TemporalAdjusterInterface
        {
            /**
             * @var int
             */
            private $value;

            public function __construct(int $value)
            {
                $this->value = $value;
            }

            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                $dayOfWeek = $temporal->get(ChronoField::dayOfWeek());

                if ($dayOfWeek === $this->value) {
                    return $temporal;
                }

                $daysDiff = $dayOfWeek - $this->value;
                return $temporal->plus($daysDiff >= 0 ? 7 - $daysDiff : -$daysDiff, ChronoUnit::days());
            }
        };
    }

    public static function previous(DayOfWeek $dayOfWeek) : TemporalAdjusterInterface
    {
        return self::$cache['previous'] ?? self::$cache['previous'] = new class($dayOfWeek->getValue()) implements
            TemporalAdjusterInterface
        {
            /**
             * @var int
             */
            private $value;

            public function __construct(int $value)
            {
                $this->value = $value;
            }

            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                $dayOfWeek = $temporal->get(ChronoField::dayOfWeek());
                $daysDiff = $this->value - $dayOfWeek;
                return $temporal->minus($daysDiff >= 0 ? 7 - $daysDiff : -$daysDiff, ChronoUnit::days());
            }
        };
    }

    public static function previousOrSame(DayOfWeek $dayOfWeek) : TemporalAdjusterInterface
    {
        return self::$cache['previousOrSame'] ?? self::$cache['previousOrSame'] = new class(
            $dayOfWeek->getValue()
        ) implements
            TemporalAdjusterInterface
        {
            /**
             * @var int
             */
            private $value;

            public function __construct(int $value)
            {
                $this->value = $value;
            }

            public function adjustInto(TemporalInterface $temporal) : TemporalInterface
            {
                $dayOfWeek = $temporal->get(ChronoField::dayOfWeek());

                if ($dayOfWeek === $this->value) {
                    return $temporal;
                }

                $daysDiff = $this->value - $dayOfWeek;
                return $temporal->minus($daysDiff >= 0 ? 7 - $daysDiff : -$daysDiff, ChronoUnit::days());
            }
        };
    }
}
