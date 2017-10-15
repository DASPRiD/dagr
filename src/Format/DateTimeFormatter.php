<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\Chrono\AbstractChronology;
use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;
use Dagr\Temporal\TemporalAccessorInterface;
use Dagr\Temporal\TemporalFieldInterface;
use Dagr\Temporal\TemporalQueryInterface;
use Dagr\TimeZone\AbstractTimeZone;

final class DateTimeFormatter
{
    use NotClonableTrait;
    use NotSerializableTrait;

    /**
     * @var CompositePrinterParser
     */
    private $printerParser;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var DecimalStyle
     */
    private $decimalStyle;

    /**
     * @var ResolverStyle|null
     */
    private $resolverStyle;

    /**
     * @var TemporalFieldInterface[]
     */
    private $resolverFields;

    /**
     * @var AbstractChronology
     */
    private $chrono;

    /**
     * @var AbstractTimeZone|null
     */
    private $zone;

    public function __construct(
        CompositePrinterParser $printerParser,
        string $locale,
        DecimalStyle $decimalStyle,
        ?ResolverStyle $resolverStyle,
        array $resolverFields,
        ?AbstractChronology $chrono,
        ?AbstractTimeZone $zone
    ) {
        $this->printerParser = $printerParser;
        $this->locale = $locale;
        $this->decimalStyle = $decimalStyle;
        $this->resolverStyle = $resolverStyle;
        $this->resolverFields = $resolverFields;
        $this->chrono = $chrono;
        $this->zone = $zone;
    }

    public function getLocale() : string
    {
        return $this->locale;
    }

    public function withLocale(string $locale) : self
    {
        if ($this->locale === $locale) {
            return $this;
        }

        return new self(
            $this->printerParser,
            $locale,
            $this->decimalStyle,
            $this->resolverStyle,
            $this->resolverFields,
            $this->chrono,
            $this->zone
        );
    }

    public function getDecimalStyle() : DecimalStyle
    {
        return $this->decimalStyle;
    }

    public function withDecimalStyle(DecimalStyle $decimalStyle) : self
    {
        if ($this->decimalStyle->equals($decimalStyle)) {
            return $this;
        }

        return new self(
            $this->printerParser,
            $this->locale,
            $decimalStyle,
            $this->resolverStyle,
            $this->resolverFields,
            $this->chrono,
            $this->zone
        );
    }

    public function getChronology() : ?AbstractChronology
    {
        return $this->chrono;
    }

    public function withChronology(AbstractChronology $chrono) : self
    {
        if ($this->chrono->equals($chrono)) {
            return $this;
        }

        return new self(
            $this->printerParser,
            $this->locale,
            $this->decimalStyle,
            $this->resolverStyle,
            $this->resolverFields,
            $chrono,
            $this->zone
        );
    }

    public function getZone() : ?AbstractTimeZone
    {
        return $this->zone;
    }

    public function withZone(AbstractTimeZone $zone) : self
    {
        if ($this->zone->equals($zone)) {
            return $this;
        }

        return new self(
            $this->printerParser,
            $this->locale,
            $this->decimalStyle,
            $this->resolverStyle,
            $this->resolverFields,
            $this->chrono,
            $zone
        );
    }

    public function getResolverStyle() : ?ResolverStyle
    {
        return $this->resolverStyle;
    }

    public function withResolverStyle(ResolverStyle $resolverStyle) : self
    {
        if ($this->resolverStyle->equals($resolverStyle)) {
            return $this;
        }

        return new self(
            $this->printerParser,
            $this->locale,
            $this->decimalStyle,
            $resolverStyle,
            $this->resolverFields,
            $this->chrono,
            $this->zone
        );
    }

    public function withResolverFields(TemporalFieldInterface ...$resolverFields) : self
    {
        if ($resolverFields === $this->resolverFields) {
            return $this;
        }

        return new self(
            $this->printerParser,
            $this->locale,
            $this->decimalStyle,
            $this->resolverStyle,
            $resolverFields,
            $this->chrono,
            $this->zone
        );
    }

    public function format(TemporalAccessorInterface $temporal) : string
    {
        $context = new DateTimePrintContext($temporal, $this);
        return $this->printerParser->format($context);
    }

    public function parse(string $text, int $position = null) : TemporalAccessorInterface
    {
    }

    public function parseBest(string $text, TemporalQueryInterface ...$queries) : TemporalAccessorInterface
    {
    }

    public function toPrinterParser(bool $optional) : CompositePrinterParser
    {
        return $this->printerParser->withOptional($optional);
    }

    public function __toString() : string
    {
        $pattern = (string) $this->printerParser;
        $pattern = (0 === strpos($pattern, '[')) ? $pattern : substr($pattern, 1, -1);
        return $pattern;
    }
}
