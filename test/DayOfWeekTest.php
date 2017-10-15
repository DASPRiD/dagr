<?php
declare(strict_types = 1);

namespace DagrTest;

use Dagr\DayOfWeek;
use Dagr\Exception\DateTimeException;
use Dagr\Exception\ExceptionInterface;
use Dagr\Format\TextStyle;
use Dagr\LocalTime;
use Dagr\Temporal\ChronoField;
use Dagr\Temporal\ChronoUnit;
use Dagr\Temporal\JulianField;
use Dagr\Temporal\TemporalAccessorInterface;
use Dagr\Temporal\TemporalQueries;
use Dagr\Temporal\TemporalQueryInterface;

final class DayOfWeekTest extends AbstractDateTimeTest
{
    public function samples() : array
    {
        return [
            DayOfWeek::monday(),
            DayOfWeek::wednesday(),
            DayOfWeek::sunday(),
        ];
    }

    public function validFields() : array
    {
        return [
            ChronoField::dayOfWeek(),
        ];
    }

    public function invalidFields() : array
    {
        $fields = array_diff(ChronoField::values(), $this->validFields());
        $fields += [
            JulianField::julianDay(),
            JulianField::modifiedJulianDay(),
            JulianField::rataDie(),
        ];
        return $fields;
    }

    public function testSingletons() : void
    {
        for ($i = 1; $i <= 7; ++$i) {
            $test = DayOfWeek::of($i);
            $this->assertSame($i, $test->getValue());
            $this->assertSame(DayOfWeek::of($i), $test);
        }
    }

    public function testFactoryIntValueTooLow() : void
    {
        $this->expectException(DateTimeException::class);
        DayOfWeek::of(0);
    }

    public function testFactoryIntValueTooHigh() : void
    {
        $this->expectException(DateTimeException::class);
        DayOfWeek::of(8);
    }

    public function testCalendricalObjectFactory() : void
    {
        // @todo Requires LocalDate to be functional
    }

    public function testCalendricalObjectFactoryInvalidNoDerive() : void
    {
        $this->expectException(ExceptionInterface::class);
        DayOfWeek::from(LocalTime::of(12, 30));
    }

    public function testGetTemporalField() : void
    {
        $this->assertSame(3, DayOfWeek::wednesday()->get(ChronoField::dayOfWeek()));
    }

    public function testGetIntTemporalField() : void
    {
        $this->assertSame(3, DayOfWeek::wednesday()->getInt(ChronoField::dayOfWeek()));
    }

    public function queries() : array
    {
        return [
            [DayOfWeek::friday(), TemporalQueries::chronology(), null],
            [DayOfWeek::friday(), TemporalQueries::zoneId(), null],
            [DayOfWeek::friday(), TemporalQueries::precision(), ChronoUnit::days()],
            [DayOfWeek::friday(), TemporalQueries::zone(), null],
            [DayOfWeek::friday(), TemporalQueries::offset(), null],
            [DayOfWeek::friday(), TemporalQueries::localDate(), null],
            [DayOfWeek::friday(), TemporalQueries::localTime(), null],
        ];
    }

    /**
     * @dataProvider queries
     * @param mixed $expected
     */
    public function testQuery(
        TemporalAccessorInterface $temporal,
        TemporalQueryInterface $query,
        $expected
    ) : void {
        $this->assertSame($expected, $temporal->query($query));
    }

    /**
     * @dataProvider queries
     * @param mixed $expected
     */
    public function testQueryFrom(
        TemporalAccessorInterface $temporal,
        TemporalQueryInterface $query,
        $expected
    ) : void {
        $this->assertSame($expected, $query->queryFrom($temporal));
    }

    public function plus() : array
    {
        return [
            [1, -8, 7],
            [1, -7, 1],
            [1, -6, 2],
            [1, -5, 3],
            [1, -4, 4],
            [1, -3, 5],
            [1, -2, 6],
            [1, -1, 7],
            [1, 0, 1],
            [1, 1, 2],
            [1, 2, 3],
            [1, 3, 4],
            [1, 4, 5],
            [1, 5, 6],
            [1, 6, 7],
            [1, 7, 1],
            [1, 8, 2],

            [1, 1, 2],
            [2, 1, 3],
            [3, 1, 4],
            [4, 1, 5],
            [5, 1, 6],
            [6, 1, 7],
            [7, 1, 1],

            [1, -1, 7],
            [2, -1, 1],
            [3, -1, 2],
            [4, -1, 3],
            [5, -1, 4],
            [6, -1, 5],
            [7, -1, 6],
        ];
    }

    public function testGetText() : void
    {
        self::assertSame('Mon', DayOfWeek::monday()->getDisplayName(TextStyle::short(), 'en-US'));
    }

    /**
     * @dataProvider plus
     */
    public function testPlus(int $base, int $amount, int $expected) : void
    {
        $this->assertSame(DayOfWeek::of($expected), DayOfWeek::of($base)->plus($amount));
    }

    public function minus() : array
    {
        return [
            [1, -8, 2],
            [1, -7, 1],
            [1, -6, 7],
            [1, -5, 6],
            [1, -4, 5],
            [1, -3, 4],
            [1, -2, 3],
            [1, -1, 2],
            [1, 0, 1],
            [1, 1, 7],
            [1, 2, 6],
            [1, 3, 5],
            [1, 4, 4],
            [1, 5, 3],
            [1, 6, 2],
            [1, 7, 1],
            [1, 8, 7],
        ];
    }

    /**
     * @dataProvider minus
     */
    public function testMinus(int $base, int $amount, int $expected) : void
    {
        $this->assertSame(DayOfWeek::of($expected), DayOfWeek::of($base)->minus($amount));
    }

    public function testAdjustInto() : void
    {
        // @todo Requires LocalDate to be functional
    }

    public function testToString() : void
    {
        $this->assertSame('Monday', (string) DayOfWeek::monday());
        $this->assertSame('Tuesday', (string) DayOfWeek::tuesday());
        $this->assertSame('Wednesday', (string) DayOfWeek::wednesday());
        $this->assertSame('Thursday', (string) DayOfWeek::thursday());
        $this->assertSame('Friday', (string) DayOfWeek::friday());
        $this->assertSame('Saturday', (string) DayOfWeek::saturday());
        $this->assertSame('Sunday', (string) DayOfWeek::sunday());
    }
}
