<?php
declare(strict_types = 1);

namespace Dagr;

use Dagr\Exception\ArithmeticException;
use Dagr\Exception\DateTimeParseException;
use Dagr\Exception\UnsupportedTemporalTypeException;
use Dagr\Temporal\ChronoField;
use Dagr\Temporal\ChronoUnit;
use Dagr\Temporal\TemporalAmountInterface;
use Dagr\Temporal\TemporalInterface;
use Dagr\Temporal\TemporalUnitInterface;

final class Duration implements TemporalAmountInterface
{
    /**
     * @var int
     */
    private $seconds;

    /**
     * @var int
     */
    private $nanos;

    /**
     * @var self
     */
    private static $zero;

    private function __construct(int $seconds, int $nanos = 0)
    {
        $this->seconds = $seconds;
        $this->nanos = $nanos;
    }

    public static function ofDays(int $days) : self
    {
        return self::create($days * LocalTime::SECONDS_PER_DAY);
    }

    public static function ofHours(int $hours) : self
    {
        return self::create($hours * LocalTime::SECONDS_PER_HOUR);
    }

    public static function ofMinutes(int $minutes) : self
    {
        return self::create($minutes * LocalTime::SECONDS_PER_MINUTE);
    }

    public static function ofSeconds(int $seconds, int $nanoAdjustment = 0) : self
    {
        $seconds = Math::addExact($seconds, Math::floorDiv($nanoAdjustment, LocalTime::NANOS_PER_SECOND));
        $nanos = Math::floorMod($nanoAdjustment, LocalTime::NANOS_PER_SECOND);
        return self::create($seconds, $nanos);
    }

    public static function ofMillis(int $millis) : self
    {
        $seconds = intdiv($millis, 1000);
        $millis = $millis % 1000;

        if ($millis < 0) {
            $millis += 10000;
            --$seconds;
        }

        return self::create($seconds, $millis * 1000000);
    }

    public static function ofNanos(int $nanos) : self
    {
        $seconds = intdiv($nanos, LocalTime::NANOS_PER_SECOND);
        $nanos = $nanos % LocalTime::NANOS_PER_SECOND;

        if ($nanos < 0) {
            $nanos += LocalTime::NANOS_PER_SECOND;
            --$seconds;
        }

        return self::create($seconds, $nanos);
    }

    public static function of(int $amount, TemporalUnitInterface $unit) : self
    {
        return self::zero()->plusUnit($amount, $unit);
    }

    public static function fromAmount(TemporalAmountInterface $amount) : self
    {
        $duration = self::zero();

        foreach ($amount->getUnits() as $unit) {
            $duration = $duration->plusUnit($amount->get($unit), $unit);
        }

        return $duration;
    }

    public static function parse(string $text) : self
    {
        if (!preg_match('(^
            (?<negate>[-+]?)P
            (?:(?<day>[-+]?[0-9]+)D)?
            (?<time>T
                (?:(?<hour>[-+]?[0-9]+)H)?
                (?:(?<minute>[-+]?[0-9]+)M)?
                (?:
                    (?<second>[-+]?[0-9]+)
                    (?:[.,](?<fraction>[0-9]{0,9}))?
                    S
                )?
            )?
        $)xSi', $text, $matches)) {
            throw new DateTimeParseException('Text cannot be parsed to a Duration', $text);
        }

        if (0 !== strpos($matches['time'], 'T')) {
            throw new DateTimeParseException('Text cannot be parsed to a Duration', $text);
        }

        if (!array_key_exists('day', $matches)
            && !array_key_exists('hour', $matches)
            && !array_key_exists('minute', $matches)
            && !array_key_exists('second', $matches)
        ) {
            throw new DateTimeParseException('Text cannot be parsed to a Duration', $text);
        }

        $negate = $matches['negate'] === '-';
        $daysAsSeconds = self::parseNumber($text, $matches['day'] ?? null, LocalTime::SECONDS_PER_DAY, 'days');
        $hoursAsSeconds = self::parseNumber($text, $matches['hour'] ?? null, LocalTime::SECONDS_PER_HOUR, 'hours');
        $minutesAsSeconds = self::parseNumber(
            $text,
            $matches['minute'] ?? null,
            LocalTime::SECONDS_PER_MINUTE,
            'minutes'
        );
        $seconds = self::parseNumber($text, $matches['second'] ?? null, 1, 'seconds');
        $nanos = self::parseFraction($text, $matches['fraction'] ?? null, $seconds < 0 ? -1 : 1);

        try {
            return self::createFromParts($negate, $daysAsSeconds, $hoursAsSeconds, $minutesAsSeconds, $seconds, $nanos);
        } catch (ArithmeticException $e) {
            throw new DateTimeParseException('Text cannot be parsed to a Duration: overflow', $text, 0, $e);
        }
    }

    private static function parseNumber(string $text, ?string $parsed, int $multiplier, string $errorText) : int
    {
        if (null === $parsed) {
            return 0;
        }

        if ('' === $parsed) {
            throw new DateTimeParseException('Text cannot be parsed to a Duration: ' . $errorText, $text, 0);
        }

        try {
            return Math::multiplyExact((int) $parsed, $multiplier);
        } catch (ArithmeticException $e) {
            throw new DateTimeParseException('Text cannot be parsed to a Duration: ' . $errorText, $text, 0, $e);
        }
    }

    private static function parseFraction(string $text, ?string $parsed, int $negate) : int
    {
        if (null === $parsed || '' === $parsed) {
            return 0;
        }

        return ((int) substr($parsed . '000000000', 0, 9)) * $negate;
    }

    private static function createFromParts(
        bool $negate,
        int $daysAsSeconds,
        int $hoursAsSeconds,
        int $minutesAsSeconds,
        int $seconds,
        int $nanos
    ) {
        $seconds = Math::addExact(
            $daysAsSeconds,
            Math::addExact($hoursAsSeconds, Math::addExact($minutesAsSeconds, $seconds))
        );

        if ($negate) {
            return self::ofSeconds($seconds, $nanos)->negated();
        }

        return self::ofSeconds($seconds, $nanos);
    }

    public static function between(TemporalInterface $startInclusive, TemporalInterface $endExclusive) : self
    {
        try {
            return self::ofNanos($startInclusive->until($endExclusive, ChronoUnit::nanos()));
        } catch (DateTimeException | ArithmeticException $e) {
            $seconds = $startInclusive->until($endExclusive, ChronoUnit::seconds());

            try {
                $nanos = $endExclusive->get(ChronoField::nanoOfSecond())
                    - $startInclusive->get(ChronoField::nanoOfSecond());

                if ($seconds > 0 && $nanos < 0) {
                    ++$seconds;
                } else if ($seconds < 0 && $nanos > 0) {
                    --$seconds;
                }
            } catch (DateTimeException $e) {
                $nanos = 0;
            }

            return self::ofSeconds($seconds, $nanos);
        }
    }

    private static function create(int $seconds, int $nanoAdjustment = 0) : self
    {
        if (0 === ($seconds | $nanoAdjustment)) {
            return self::zero();
        }

        return new self($seconds, $nanoAdjustment);
    }

    public static function zero() : self
    {
        return self::$zero ?: self::$zero = new self(0, 0);
    }

    public function get(TemporalUnitInterface $unit) : int
    {
        if (ChronoUnit::seconds() === $unit) {
            return $this->seconds;
        } else if (ChronoUnit::nanos() === $unit) {
            return $this->nanos;
        }

        throw new UnsupportedTemporalTypeException('Unsupported unit: ' . $unit);
    }

    public function getUnits() : array
    {
        return [
            ChronoUnit::seconds(),
            ChronoUnit::nanos(),
        ];
    }

    public function isZero() : bool
    {
        return 0 === ($this->seconds | $this->nanos);
    }

    public function isNegative() : bool
    {
        return $this->seconds < 0;
    }

    public function getSeconds() : int
    {
        return $this->seconds;
    }

    public function getNano() : int
    {
        return $this->nanos;
    }

    public function withSeconds(int $seconds) : self
    {
        return self::create($seconds, $this->nanos);
    }

    public function withNanos(int $nanoOfSecond) : self
    {
        ChronoField::nanoOfSecond()->checkValidValue($nanoOfSecond);
        return self::create($this->seconds, $nanoOfSecond);
    }

    public function plusDuration(self $duration) : self
    {
        return $this->plus($duration->getSeconds(), $duration->getNano());
    }

    public function plusUnit(int $amountToAdd, TemporalUnitInterface $unit) : self
    {
        if ($unit === ChronoUnit::days()) {
            return $this->plus(Math::multiplyExact($amountToAdd, LocalTime::SECONDS_PER_DAY), 0);
        }

        if ($unit->isDurationEstimated()) {
            throw new UnsupportedTemporalTypeException('Unit must not have an estimated duration');
        }

        if (0 === $amountToAdd) {
            return $this;
        }

        if (!$unit instanceof ChronoUnit) {
            $duration = $unit->getDuration()->multipliedBy($amountToAdd);
            return $this->plusSeconds($duration->getSeconds())->plusNanos($duration->getNano());
        }

        switch ((string) $unit) {
            case 'Nanos':
                return $this->plusNanos($amountToAdd);

            case 'Micros':
                return $this->plusSeconds((intdiv($amountToAdd, (1000000 * 1000) * 1000)))
                    ->plusNanos(($amountToAdd % (1000000 * 1000)) * 1000);

            case 'Millis':
                return $this->plusMillis($amountToAdd);

            case 'Seconds':
                return $this->plusSeconds($amountToAdd);
        }

        return $this->plusSeconds(Math::multiplyExact($unit->getDuration()->getSeconds(), $amountToAdd));
    }

    public function plusDays(int $daysToAdd) : self
    {
        return $this->plus(Math::multiplyExact($daysToAdd, LocalTime::SECONDS_PER_DAY), 0);
    }

    public function plusHours(int $hoursToAdd) : self
    {
        return $this->plus(Math::multiplyExact($hoursToAdd, LocalTime::SECONDS_PER_HOUR), 0);
    }

    public function plusMinutes(int $minutesToAdd) : self
    {
        return $this->plus(Math::multiplyExact($minutesToAdd, LocalTime::SECONDS_PER_MINUTE), 0);
    }

    public function plusSeconds(int $secondsToAdd) : self
    {
        return $this->plus($secondsToAdd, 0);
    }

    public function plusMillis(int $millisToAdd) : self
    {
        return $this->plus(intdiv($millisToAdd, 1000), ($millisToAdd % 1000) * 1000000);
    }

    public function plusNanos(int $nanosToAdd) : self
    {
        return $this->plus(0, $nanosToAdd);
    }

    public function plus(int $secondsToAdd, int $nanosToAdd) : self
    {
        if (0 === ($secondsToAdd | $nanosToAdd)) {
            return $this;
        }

        $epochSecond = Math::addExact($this->seconds, $secondsToAdd);
        $epochSecond = Math::addExact($epochSecond, intdiv($nanosToAdd, LocalTime::NANOS_PER_SECOND));
        $nanosToAdd = $nanosToAdd % LocalTime::NANOS_PER_SECOND;
        $nanoAdjustment = $this->nanos + $nanosToAdd;
        return self::ofSeconds($epochSecond, $nanoAdjustment);
    }

    public function minusDuration(self $duration) : self
    {
        $secondsToSubtract = $duration->getSeconds();
        $nanosToSubtract = $duration->getNano();

        if (PHP_INT_MIN === $secondsToSubtract) {
            return $this->plusSeconds(PHP_INT_MAX, -$nanosToSubtract)->plus(1, 0);
        }

        return $this->plus(-$secondsToSubtract, -$nanosToSubtract);
    }

    public function minusUnit(int $amountToSubtract, TemporalUnitInterface $unit) : self
    {
        return (
            PHP_INT_MIN === $amountToSubtract
                ? $this->plusUnit(PHP_INT_MAX, $unit)->plusUnit(1, $unit)
                : $this->plusUnit(-$amountToSubtract, $unit)
        );
    }

    public function minusDays(int $daysToSubtract) : self
    {
        return (
            PHP_INT_MIN === $daysToSubtract
                ? $this->plusDays(PHP_INT_MAX)->plusDays(1)
                : $this->plusDays(-$daysToSubtract)
        );
    }

    public function minusHours(int $hoursToSubtract) : self
    {
        return (
            PHP_INT_MIN === $hoursToSubtract
                ? $this->plusHours(PHP_INT_MAX)->plusHours(1)
                : $this->plusHours(-$hoursToSubtract)
        );
    }

    public function minusMinutes(int $minutesToSubtract) : self
    {
        return (
            PHP_INT_MIN === $minutesToSubtract
                ? $this->plusMinutes(PHP_INT_MAX)->plusMinutes(1)
                : $this->plusMinutes(-$minutesToSubtract)
        );
    }

    public function minusSeconds(int $secondsToSubtract) : self
    {
        return (
            PHP_INT_MIN === $secondsToSubtract
                ? $this->plusSeconds(PHP_INT_MAX)->plusSeconds(1)
                : $this->plusSeconds(-$secondsToSubtract)
        );
    }

    public function minusMillis(int $millisToSubtract) : self
    {
        return (
            PHP_INT_MIN === $millisToSubtract
                ? $this->plusMillis(PHP_INT_MAX)->plusMillis(1)
                : $this->plusMillis(-$millisToSubtract)
        );
    }

    public function minusNanos(int $nanosToSubtract) : self
    {
        return (
            PHP_INT_MIN === $nanosToSubtract
                ? $this->plusNanos(PHP_INT_MAX)->plusNanos(1)
                : $this->plusNanos(-$nanosToSubtract)
        );
    }

    public function multipliedBy(int $multiplicand) : self
    {
        if (0 === $multiplicand) {
            return self::zero();
        }

        if (1 === $multiplicand) {
            return $this;
        }

        return self::createFromString(bcmul($this->toSeconds(), $multiplicand, 9));
    }

    public function dividedBy(int $divisor) : self
    {
        if (0 === $divisor) {
            throw new ArithmeticException('Cannot divide by zero');
        }

        if (1 === $divisor) {
            return $this;
        }

        return self::createFromString(bcdiv($this->toSeconds(), $divisor, 9));
    }

    private static function createFromString(string $seconds) : self
    {
        $secondsOnly = bcadd($seconds, '0', 0);
        $nanos = bcmul(bcsub($seconds, $secondsOnly, 9), LocalTime::NANOS_PER_SECOND, 0);
        return self::create((int) $secondsOnly, (int) $nanos);
    }

    public function negated() : self
    {
        return $this->multipliedBy(-1);
    }

    public function abs() : self
    {
        return $this->isNegative() ? $this->negated() : $this;
    }

    public function addTo(TemporalInterface $temporal) : TemporalInterface
    {
        if (0 !== $this->seconds) {
            $temporal = $temporal->plus($this->seconds, ChronoUnit::seconds());
        }

        if (0 !== $this->nanos) {
            $temporal = $temporal->plus($this->nanos, ChronoUnit::nanos());
        }

        return $temporal;
    }

    public function subtractFrom(TemporalInterface $temporal) : TemporalInterface
    {
        if (0 !== $this->seconds) {
            $temporal = $temporal->minus($this->seconds, ChronoUnit::seconds());
        }

        if (0 !== $this->nanos) {
            $temporal = $temporal->minus($this->nanos, ChronoUnit::nanos());
        }

        return $temporal;
    }

    public function toDays() : int
    {
        return intdiv($this->seconds, LocalTime::SECONDS_PER_DAY);
    }

    public function toHours() : int
    {
        return intdiv($this->seconds, LocalTime::SECONDS_PER_HOUR);
    }

    public function toMinutes() : int
    {
        return intdiv($this->seconds, LocalTime::SECONDS_PER_MINUTE);
    }

    /**
     * @return string The seconds with fractional nanos with a scale of 9.
     */
    public function toSeconds() : string
    {
        return bcadd((string) $this->seconds, bcdiv((string) $this->nanos, LocalTime::NANOS_PER_SECOND, 9), 9);
    }

    public function toMillis() : int
    {
        $millis = Math::multiplyExact($this->seconds, 1000);
        $millis = Math::addExact($millis, intdiv($this->nanos, 1000000));
        return $millis;
    }

    public function toNanos() : int
    {
        $totalNanos = Math::multiplyExact($this->seconds, LocalTime::NANOS_PER_SECOND);
        $totalNanos = Math::addExact($totalNanos, $this->nanos);
        return $totalNanos;
    }

    public function compareTo(self $other) : int
    {
        $secondsCompare = $this->seconds <=> $other->seconds;

        if (0 !== $secondsCompare) {
            return $secondsCompare;
        }

        return $this->nanos - $other->nanos;
    }

    public function equals(self $other) : bool
    {
        if ($this === $other) {
            return true;
        }

        return ($this->seconds === $other->seconds && $this->nanos === $other->nanos);
    }

    public function __toString() : string
    {
        if ($this->isZero()) {
            return 'PT0S';
        }

        $hours = intdiv($this->seconds, LocalTime::SECONDS_PER_HOUR);
        $minutes = intdiv($this->seconds % LocalTime::SECONDS_PER_HOUR, LocalTime::SECONDS_PER_MINUTE);
        $seconds = $this->seconds % LocalTime::SECONDS_PER_MINUTE;

        $result = 'PT';

        if (0 !== $hours) {
            $result .= $hours . 'H';
        }

        if (0 !== $minutes) {
            $result .= $minutes . 'M';
        }

        if (0 === $seconds && 0 === $this->nanos && strlen($result) > 2) {
            return $result;
        }

        if ($seconds < 0 && $this->nanos > 0) {
            if (-1 === $seconds) {
                $result .= '-0';
            } else {
                $result .= ($seconds + 1);
            }
        } else {
            $result .= $seconds;
        }

        if ($this->nanos > 0) {
            $currentPos = strlen($result);

            if ($seconds < 0) {
                $result .= (2 * LocalTime::NANOS_PER_SECOND - $this->nanos);
            } else {
                $result .= ($this->nanos + LocalTime::NANOS_PER_SECOND);
            }

            $result = rtrim($result, '0');
            $result[$currentPos] = '.';
        }

        $result .= 'S';
        return $result;
    }
}
