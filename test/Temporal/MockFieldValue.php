<?php
declare(strict_types = 1);

namespace DagrTest\Temporal;

use Dagr\Exception\UnsupportedTemporalTypeException;
use Dagr\Temporal\ChronoField;
use Dagr\Temporal\DefaultTemporalAccessorTrait;
use Dagr\Temporal\TemporalAccessorInterface;
use Dagr\Temporal\TemporalFieldInterface;
use Dagr\Temporal\ValueRange;

final class MockFieldValue implements TemporalAccessorInterface
{
    use DefaultTemporalAccessorTrait;

    /**
     * @var TemporalFieldInterface
     */
    private $field;

    /**
     * @var int
     */
    private $value;

    public function __construct(TemporalFieldInterface $field, int $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function isSupportedField(TemporalFieldInterface $field) : bool
    {
        return $field == $this->field;
    }

    public function range(TemporalFieldInterface $field) : ValueRange
    {
        if (!$field instanceof ChronoField) {
            return $field->rangeRefinedBy($this);
        }

        if ($this->isSupportedField($field)) {
            return $field->range();
        }

        throw new UnsupportedTemporalTypeException('Unsupported field: ' . $field);
    }

    public function getInt(TemporalFieldInterface $field) : int
    {
        if ($field == $this->field) {
            return $this->value;
        }

        throw new UnsupportedTemporalTypeException('Unsupported field: ' . $field);
    }

    public function __toString() : string
    {
        return '';
    }
}
