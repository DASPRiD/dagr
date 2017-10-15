<?php
declare(strict_types = 1);

namespace Dagr;

use Dagr\Exception\DateTimeException;
use Dagr\Exception\UnsupportedTemporalTypeException;
use Dagr\Format\DateTimeFormatterBuilder;
use Dagr\Format\TextStyle;
use Dagr\Temporal\ChronoField;
use Dagr\Temporal\ChronoUnit;
use Dagr\Temporal\DefaultTemporalAccessorTrait;
use Dagr\Temporal\TemporalAccessorInterface;
use Dagr\Temporal\TemporalAdjusterInterface;
use Dagr\Temporal\TemporalFieldInterface;
use Dagr\Temporal\TemporalInterface;
use Dagr\Temporal\TemporalQueries;
use Dagr\Temporal\TemporalQueryInterface;
use Dagr\Temporal\ValueRange;

final class DayOfWeek implements TemporalAccessorInterface, TemporalAdjusterInterface
{
    use NotClonableTrait;
    use NotSerializableTrait;
    use DefaultTemporalAccessorTrait {
        range as private defaultRange;
        get as private defaultGet;
        query as private defaultQuery;
    }

    private const INDEX_MAP = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $index;

    /**
     * @var self[]
     */
    private static $cache;

    public function __construct(string $name, int $index)
    {
        $this->name = $name;
        $this->index = $index;
    }

    public static function of(int $dayOfWeek) : self
    {
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            throw new DateTimeException('Invalid value for DayOfWeek: ' . $dayOfWeek);
        }

        $method = self::INDEX_MAP[$dayOfWeek - 1];
        return self::{$method}();
    }

    public static function from(TemporalAccessorInterface $temporal) : self
    {
        if ($temporal instanceof self) {
            return $temporal;
        }

        try {
            return self::of($temporal->get(ChronoField::dayOfWeek()));
        } catch (DateTimeException $e) {
            throw new DateTimeException(
                'Unable to obtain DayOfWeek from TemporalAccessor: ' . $temporal  . ' of type ' . get_class($temporal),
                0,
                $e
            );
        }
    }

    public function getValue() : int
    {
        return $this->index + 1;
    }

    public function getDisplayName(TextStyle $style, string $locale) : string
    {
        return (new DateTimeFormatterBuilder())
            ->appendText(ChronoField::dayOfWeek(), $style)
            ->toFormatter($locale)
            ->format($this);
    }

    public function isSupportedField(TemporalFieldInterface $field) : bool
    {
        if ($field instanceof ChronoField) {
            return ChronoField::dayOfWeek() === $field;
        }

        return $field->isSupportedBy($this);
    }

    public function range(TemporalFieldInterface $field) : ValueRange
    {
        if (ChronoField::dayOfWeek() === $field) {
            return $field->range();
        }

        return $this->defaultRange($field);
    }

    public function get(TemporalFieldInterface $field) : int
    {
        if (ChronoField::dayOfWeek() === $field) {
            return $this->getValue();
        }

        return $this->defaultGet($field);
    }

    public function getInt(TemporalFieldInterface $field) : int
    {
        if (ChronoField::dayOfWeek() === $field) {
            return $this->getValue();
        }

        if ($field instanceof ChronoField) {
            throw new UnsupportedTemporalTypeException('Unsupported field: ' . $field);
        }

        return $field->getFrom($this);
    }

    public function plus(int $days) : self
    {
        $amount = $days % 7;
        $method = self::INDEX_MAP[($this->index + ($amount + 7)) % 7];
        return self::{$method}();
    }

    public function minus(int $days) : self
    {
        return $this->plus(-($days % 7));
    }

    public function query(TemporalQueryInterface $query)
    {
        if (TemporalQueries::precision() === $query) {
            return ChronoUnit::days();
        }

        return $this->defaultQuery($query);
    }

    /**
     * @return self Workaround for invariant return type
     */
    public function adjustInto(TemporalInterface $temporal) : TemporalInterface
    {
        $newDayOfWeek = $temporal->withField(ChronoField::dayOfWeek(), $this->getValue());
        assert($newDayOfWeek instanceof self);
        return $newDayOfWeek;
    }

    public function __toString() : string
    {
        return $this->name;
    }

    public static function monday() : self
    {
        return self::$cache['monday'] ?? self::$cache['monday'] = new self('Monday', 0);
    }

    public static function tuesday() : self
    {
        return self::$cache['tuesday'] ?? self::$cache['tuesday'] = new self('Tuesday', 1);
    }

    public static function wednesday() : self
    {
        return self::$cache['wednesday'] ?? self::$cache['wednesday'] = new self('Wednesday', 2);
    }

    public static function thursday() : self
    {
        return self::$cache['thursday'] ?? self::$cache['thursday'] = new self('Thursday', 3);
    }

    public static function friday() : self
    {
        return self::$cache['friday'] ?? self::$cache['friday'] = new self('Friday', 4);
    }

    public static function saturday() : self
    {
        return self::$cache['saturday'] ?? self::$cache['saturday'] = new self('Saturday', 5);
    }

    public static function sunday() : self
    {
        return self::$cache['sunday'] ?? self::$cache['sunday'] = new self('Sunday', 6);
    }
}
