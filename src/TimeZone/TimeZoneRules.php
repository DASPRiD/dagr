<?php
declare(strict_types = 1);

namespace Dagr\TimeZone;

use Dagr\Instant;

final class TimeZoneRules
{
    private const LAST_CACHED_YEAR = 2100;

    /**
     * @var int[]
     */
    private $standardTransitions;

    /**
     * @var int[]
     */
    private $savingsInstantTransitions;

    /**
     * @var TimeZoneOffset[]
     */
    private $standardOffsets;

    /**
     * @var LocalDateTime[]
     */
    private $savingsLocalTransitions = [];

    /**
     * @var TimeZoneOffset[]
     */
    private $wallOffsets;

    /**
     * @var TimeZoneOffsetTransitionRule[]
     */
    private $lastRules;

    /**
     * @var TimeZoneOffsetTransition[][]
     */
    private $lastRulesCache = [];

    public static function ofOffset(TimeZoneOffset $offset) : self
    {
        return new self([], [$offset], [], [], [], []);
    }

    /**
     * @param int[] $standardTransitions
     * @param TimeZoneOffset[] $standardOffsets
     * @param int[] $savingsInstantTransitions
     * @param TimeZoneOffset[] $wallOffsets
     * @param TimeZoneOffsetTransitionRule[] $lastRules
     */
    private function __construct(
        array $standardTransitions,
        array $standardOffsets,
        array $savingsInstantTransitions,
        array $wallOffsets,
        array $lastRules
    ) {
        $this->standardOffsets = $standardOffsets;
        $this->standardTransitions = $standardTransitions;
        $this->savingsInstantTransitions = $savingsInstantTransitions;
        $this->wallOffsets = $wallOffsets;
        $this->lastRules = $lastRules;

        if (empty($savingsInstantTransitions)) {
            return;
        }

        foreach ($savingsInstantTransitions as $index => $savingsInstantTransition) {
            $before = $wallOffsets[$index];
            $after = $wallOffsets[$index + 1];
            $transition = TimeZoneOffsetTransition::ofEpochSecond($savingsInstantTransition, $before, $after);

            if ($transition->isGap()) {
                $this->savingsLocalTransitions[] = $transition->getDateTimeBefore();
                $this->savingsLocalTransitions[] = $transition->getDateTimeAfter();
                continue;
            }

            $this->savingsLocalTransitions[] = $transition->getDateTimeAfter();
            $this->savingsLocalTransitions[] = $transition->getDateTimeBefore();
        }
    }

    public function getOffset(Instant $instant) : TimeZoneOffset
    {
        if (empty($this->savingsInstantTransitions)) {
            return $this->standardOffsets[0];
        }

        $epochSecond = $instant->getEpochSecond();

        if (!empty($this->lastRules) && $epochSecond > end($this->savingsInstantTransitions)) {
            $year = $this->findYear($epochSecond, end($this->wallOffsets));

            foreach ($this->findTransitions($year) as $transition) {
                if ($epochSecond < $transition->toEpochSecond()) {
                    return $transition->getOffsetBefore();
                }
            }

            return $transition->getOffsetAfter();
        }

        $index = $this->binarySearch($this->savingsInstantTransitions, $epochSecond);

        if ($index < 0) {
            $index = -$index - 2;
        }

        return $this->wallOffsets[$index + 1];
    }

    private function findYear(int $epochSecond, TimeZoneOffset $offset) : int
    {
        $localSecond = $epochSecond + $offset->getTotalSeconds();
        $localEpochDay = floor($localSecond / 86400);
        return LocalDate::ofEpochDay($localEpochDay)->getYear();
    }

    /**
     * @return TimeZoneOffsetTransition[]
     */
    private function findTransitions(int $year) : array
    {
        if (array_key_exists($year, $this->lastRulesCache)) {
            return $this->lastRulesCache[$year];
        }

        $transitions = [];

        foreach ($this->lastRules as $lastRule) {
            $transitions[] = $lastRule->createTransition($year);
        }

        if ($year < self::LAST_CACHED_YEAR) {
            $this->lastRulesCache[$year] = $transitions;
        }

        return $transitions;
    }
}
