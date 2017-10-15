<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\Chrono\IsoChronology;
use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;
use Dagr\Temporal\TemporalFieldInterface;
use Dagr\Temporal\TemporalQueries;

final class TextPrinterParser implements DateTimePrinterParserInterface
{
    use NotClonableTrait;
    use NotSerializableTrait;

    /**
     * @var TemporalFieldInterface
     */
    private $field;

    /**
     * @var TextStyle
     */
    private $textStyle;

    /**
     * @var DateTimeTextProvider
     */
    private $provider;

    /**
     * @var NumberPrinterParser|null
     */
    private $numberPrinterParser;

    public function __construct(TemporalFieldInterface $field, TextStyle $textStyle, DateTimeTextProvider $provider)
    {
        $this->field = $field;
        $this->textStyle = $textStyle;
        $this->provider = $provider;
    }

    public function format(DateTimePrintContext $context) : ?string
    {
        $value = $context->getValue($this->field);

        if (null === $value) {
            return null;
        }

        $chronology = $context->getTemporal()->query(TemporalQueries::chronology());
        $text = $this->provider->getText($this->field, $value, $this->textStyle, $context->getLocale(), $chronology);

        if (null === $text) {
            return $this->numberPrinterParser()->format($context);
        }

        return $text;
    }

    public function parse(DateTimePrintContext $context, string $parseText, int $position) : int
    {
        $length = grapheme_strlen($parseText);

        if ($position < 0 || $position > $length) {
            // @todo throw IndexOutOfBoundsException
        }

        $style = ($context->isStrict() ? $this->textStyle : null);
        $chronology = $context->getEffectiveChronology();

        if (null === $chronology || IsoChronology::getInstance() === $chronology) {
            $iterator = $this->provider->getTextIterator($this->field, $style, $context->getLocale());
        } else {
            $iterator = $this->provider->getTextIterator($chronology, $this->field, $style, $context->getLocale());
        }

        if (null === $iterator) {
            return $this->numberPrinterParser()->parse($context, $parseText, $position);
        }
    }

    private function numberPrinterParser() : NumberPrinterParser
    {
        if (null === $this->numberPrinterParser) {
            $this->numberPrinterParser = new NumberPrinterParser($this->field, 1, 19, SignStyle::normal());
        }

        return $this->numberPrinterParser;
    }

    public function __toString() : string
    {
        if (TextStyle::full() === $this->textStyle) {
            return 'Text(' . $this->field . ')';
        }

        return 'Text(' . $this->field . ',' . $this->textStyle . ')';
    }
}
