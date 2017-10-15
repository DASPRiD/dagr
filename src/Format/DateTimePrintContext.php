<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\Chrono\AbstractChronology;
use Dagr\Chrono\ChronoLocalDateInterface;
use Dagr\Chrono\IsoChronology;
use Dagr\Clock\Instant;
use Dagr\Exception\DateTimeException;
use Dagr\Exception\ExceptionInterface;
use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;
use Dagr\Objects;
use Dagr\Temporal\ChronoField;
use Dagr\Temporal\DefaultTemporalAccessorTrait;
use Dagr\Temporal\TemporalAccessorInterface;
use Dagr\Temporal\TemporalFieldInterface;
use Dagr\Temporal\TemporalQueries;
use Dagr\Temporal\TemporalQueryInterface;
use Dagr\Temporal\ValueRange;
use Dagr\TimeZone\TimeZoneId;
use Dagr\TimeZone\TimeZoneOffset;

final class DateTimePrintContext
{
    use NotClonableTrait;
    use NotSerializableTrait;

    /**
     * @var TemporalAccessorInterface
     */
    private $temporal;

    /**
     * @var DateTimeFormatter
     */
    private $formatter;

    /**
     * @var int
     */
    private $optional = 0;

    public function __construct(TemporalAccessorInterface $temporal, DateTimeFormatter $formatter)
    {
        $this->temporal = self::adjust($temporal, $formatter);
        $this->formatter = $formatter;
    }

    private static function adjust(
        TemporalAccessorInterface $temporal,
        DateTimeFormatter $formatter
    ) : TemporalAccessorInterface {
        $overrideChronology = $formatter->getChronology();
        $overrideZone = $formatter->getZone();

        if (null === $overrideChronology && null === $overrideZone) {
            return $temporal;
        }

        $temporalChronology = $temporal->query(TemporalQueries::chronology());
        $temporalZone = $temporal->query(TemporalQueries::zoneId());

        if (Objects::equals($overrideChronology, $temporalChronology)) {
            $overrideChronology = null;
        }

        if (Objects::equals($overrideZone, $temporalZone)) {
            $overrideZone = null;
        }

        if (null === $overrideChronology && null === $overrideZone) {
            return $temporal;
        }

        $effectiveChronology = (null !== $overrideChronology ? $overrideChronology : $temporalChronology);

        if (null !== $overrideChronology) {
            if ($temporal->isSupportedField(ChronoField::instantSeconds())) {
                $chronology = (null !== $effectiveChronology ? $effectiveChronology : IsoChronology::getInstance());
                return $chronology->zonedDateTime(Instant::from($temporal), $overrideZone);
            }

            if ($overrideZone->normalized() instanceof TimeZoneOffset
                && $temporal->isSupportedField(ChronoField::offsetSeconds())
                && $temporal->get(ChronoField::offsetSeconds()) !==
                $overrideZone->getRules()->getOffset(Instant::epoch())->getTotalSeconds()
            ) {
                throw new DateTimeException(
                    'Unable to apply override zone "' . $overrideZone . '" because the temporal object being formatted'
                    . ' has a different offset but does not represent an instant: ' . $temporal
                );
            }
        }

        $effectiveZone = (null !== $overrideZone ? $overrideZone : $temporalZone);
        $effectiveDate = null;

        if (null !== $overrideChronology) {
            if ($temporal->isSupportedField(ChronoField::epochDay())) {
                $effectiveDate = $effectiveChronology->date($temporal);
            } elseif (! ($overrideChronology === IsoChronology::getInstance() && null === $temporalChronology)) {
                foreach (ChronoField::values() as $field) {
                    if ($field->isDateBased() && $temporal->isSupportedField($field)) {
                        throw new DateTimeException(
                            'Unable to apply override chronology "' . $overrideChronology . '" because the temporal'
                            . ' object being formatted contains date fields but does not represent a whole date: '
                            . $temporal
                        );
                    }
                }
            }
        }

        return new class(
            $temporal,
            $effectiveDate,
            $effectiveChronology,
            $effectiveZone
        ) implements TemporalAccessorInterface
        {
            use DefaultTemporalAccessorTrait;

            /**
             * @var TemporalAccessorInterface
             */
            private $temporal;

            /**
             * @var ChronoLocalDateInterface|null
             */
            private $effectiveDate;

            /**
             * @var AbstractChronology|null
             */
            private $effectiveChronology;

            /**
             * @var TimeZoneId|null
             */
            private $effectiveZone;

            public function __construct(
                TemporalAccessorInterface $temporal,
                ?ChronoLocalDateInterface $effectiveDate,
                ?AbstractChronology $effectiveChronology,
                ?TimeZoneId $effectiveZone)
            {
                $this->temporal = $temporal;
                $this->effectiveDate = $effectiveDate;
                $this->effectiveChronology = $effectiveChronology;
                $this->effectiveZone = $effectiveZone;
            }

            public function isSupportedField(TemporalFieldInterface $field) : bool
            {
                if (null !== $this->effectiveDate && $field->isDateBased()) {
                    return $this->effectiveDate->isSupportedField($field);
                }

                return $this->temporal->isSupportedField($field);
            }

            public function range(TemporalFieldInterface $field) : ValueRange
            {
                if (null !== $this->effectiveDate && $field->isDateBased()) {
                    return $this->effectiveDate->range($field);
                }

                return $this->temporal->range($field);
            }

            public function getInt(TemporalFieldInterface $field) : int
            {
                if (null !== $this->effectiveDate && $field->isDateBased()) {
                    return $this->effectiveDate->getInt($field);
                }

                return $this->temporal->getInt($field);
            }

            public function query(TemporalQueryInterface $query)
            {
                if ($query === TemporalQueries::chronology()) {
                    return $this->effectiveChronology;
                }

                if ($query === TemporalQueries::zoneId()) {
                    return $this->effectiveZone;
                }

                if ($query === TemporalQueries::precision()) {
                    return $this->temporal->query($query);
                }

                return $query->queryFrom($this->temporal);
            }

            public function __toString() : string
            {
                return (string) $this->temporal;
            }
        };
    }

    public function getTemporal() : TemporalAccessorInterface
    {
        return $this->temporal;
    }

    public function getLocale() : string
    {
        return $this->formatter->getLocale();
    }

    public function getDecimalStyle() : DecimalStyle
    {
        return $this->formatter->getDecimalStyle();
    }

    public function startOptional() : void
    {
        ++$this->optional;
    }

    public function endOptional() : void
    {
        --$this->optional;
    }

    /**
     * @return mixed
     */
    public function query(TemporalQueryInterface $query)
    {
        $result = $this->temporal->query($query);

        if (null === $result && 0 === $this->optional) {
            throw new DateTimeException('Unable to extract value: ' . get_class($this->temporal));
        }

        return $result;
    }

    public function getValue(TemporalFieldInterface $field) : ?int
    {
        try {
            return $this->temporal->getInt($field);
        } catch (ExceptionInterface $e) {
            if ($this->optional > 0) {
                return null;
            }

            throw $e;
        }
    }

    public function __toString() : string
    {
        return (string) $this->temporal;
    }
}
