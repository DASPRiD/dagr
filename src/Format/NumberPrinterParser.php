<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\Exception\DateTimeException;
use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;
use Dagr\Temporal\TemporalFieldInterface;

final class NumberPrinterParser implements DateTimePrinterParserInterface
{
    use NotClonableTrait;
    use NotSerializableTrait;

    private const EXCEED_POINTS = [
        0,
        10,
        100,
        1000,
        10000,
        100000,
        1000000,
        10000000,
        100000000,
        1000000000,
        10000000000,
    ];

    /**
     * @var TemporalFieldInterface
     */
    private $field;

    /**
     * @var int
     */
    private $minWidth;

    /**
     * @var int
     */
    private $maxWidth;

    /**
     * @var SignStyle
     */
    private $signStyle;

    /**
     * @var int
     */
    private $subsequentWidth;

    public function __construct(
        TemporalFieldInterface $field,
        int $minWidth,
        int $maxWidth,
        SignStyle $signStyle,
        int $subsequentWidth = 0
    ) {
        $this->field = $field;
        $this->minWidth = $minWidth;
        $this->maxWidth = $maxWidth;
        $this->signStyle = $signStyle;
        $this->subsequentWidth = $subsequentWidth;
    }

    /**
     * Returns a new instance with fixed width flag set.
     */
    public function withFixedWidth() : self
    {
        if (-1 === $this->subsequentWidth) {
            return $this;
        }

        return new self($this->field, $this->minWidth, $this->maxWidth, $this->signStyle, -1);
    }

    /**
     * Returns a new instance with an updated subsequent width.
     */
    public function withSubsequentWidth(int $subsequentWidth) : self
    {
        return new self(
            $this->field,
            $this->minWidth,
            $this->maxWidth,
            $this->signStyle,
            $this->subsequentWidth + $subsequentWidth
        );
    }

    public function format(DateTimePrintContext $context) : ?string
    {
        $intValue = $context->getValue($this->field);

        if (null === $intValue) {
            return null;
        }

        $value = $this->getValue($context, $intValue);

        $decimalStyle = $context->getDecimalStyle();
        $string = ($value === PHP_INT_MIN ? '9223372036854775808' : (string) abs($value));

        if (strlen($string) > $this->maxWidth) {
            throw new DateTimeException(
                'Field ' . $this->field . ' cannot be printed as the value ' . $value
                . ' exceeds the maximum print width of ' . $this->maxWidth
            );
        }

        $result = '';
        $string = $decimalStyle->convertNumberToI18n($string);

        if ($value >= 0) {
            switch ($this->signStyle->getName()) {
                case 'exceedsPad':
                    if ($this->minWidth < 19 && $value >= self::EXCEED_POINTS[$this->minWidth]) {
                        $result .= $decimalStyle->getPositiveSign();
                    }
                    break;

                case 'always':
                    $result .= $decimalStyle->getPositiveSign();
                    break;
            }
        } else {
            switch ($this->signStyle->getName()) {
                case 'normal':
                case 'exceedsPad':
                case 'always':
                    $result .= $decimalStyle->getNegativeSign();
                    break;

                case 'notNegative':
                    throw new DateTimeException(
                        'Field ' . $this->field . ' cannot be printed as the value ' . $value . ' cannot be negative'
                        . ' according to the SignStyle'
                    );
            }
        }

        for ($i = 0; $i < $this->minWidth - grapheme_strlen($string); ++$i) {
            $result .= $decimalStyle->getZeroDigit();
        }

        $result .= $string;
        return $result;
    }

    private function getValue(DateTimePrintContext $context, int $value) : int
    {
        return $value;
    }

    public function parse(DateTimePrintContext $context, string $parseText, int $position) : int
    {
        // TODO: Implement parse() method.
    }

    private function setValue(DateTimeParseContext $context, int $value, int $errorPosition, int $successPosition) : int
    {
        return $context->setParsedField($this->field, $value, $errorPosition, $successPosition);
    }

    public function __toString() : string
    {
        if (1 === $this->minWidth && 19 === $this->maxWidth && SignStyle::normal() === $this->signStyle) {
            return 'Value(' . $this->field . ')';
        }

        if ($this->minWidth === $this->maxWidth && SignStyle::notNegative() === $this->signStyle) {
            return 'Value(' . $this->field . ',' . $this->minWidth . ')';
        }

        return 'Value(' . $this->field . ',' . $this->minWidth . ',' . $this->maxWidth . ',' . $this->signStyle . ')';
    }
}
