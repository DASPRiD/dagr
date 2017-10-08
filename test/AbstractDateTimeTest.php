<?php
declare(strict_types = 1);

namespace DagrTest;

use Dagr\Exception\ExceptionInterface;
use Dagr\Temporal\TemporalAccessorInterface;
use Dagr\Temporal\TemporalFieldInterface;
use Dagr\Temporal\TemporalInterface;
use Dagr\Temporal\TemporalQueryInterface;
use DagrTest\Temporal\MockFieldNoValue;
use PHPUnit\Framework\TestCase;

abstract class AbstractDateTimeTest extends TestCase
{
    /**
     * @return TemporalInterface[]
     */
    abstract public function samples() : array;

    /**
     * @return TemporalFieldInterface[]
     */
    abstract public function validFields() : array;

    /**
     * @return TemporalFieldInterface[]
     */
    abstract public function invalidFields() : array;

    // isSupported(TemporalFieldInterface)
    public function testBaseIsSupportedTemporalFieldSupported() : void
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->validFields() as $field) {
                self::assertTrue($sample->isSupportedField($field), 'Failed on ' . $sample . ' ' . $field);
            }
        }
    }

    public function testBaseIsSupportedTemporalFieldUnsupported() : void
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->invalidFields() as $field) {
                self::assertFalse($sample->isSupportedField($field), 'Failed on ' . $sample . ' ' . $field);
            }
        }
    }

    // range(TemporalFieldInterface)
    public function testBaseRangeTemporalFieldSupported() : void
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->validFields() as $field) {
                try {
                    $sample->range($field);
                    $this->addToAssertionCount(1);
                } catch (ExceptionInterface $e) {
                    self::fail('Failed on ' . $sample . ' ' . $field);
                }
            }
        }
    }

    public function testBaseRangeTemporalFieldUnsupported() : void
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->invalidFields() as $field) {
                try {
                    $sample->range($field);
                    self::fail('Failed on ' . $sample . ' ' . $field);
                } catch (ExceptionInterface $e) {
                    $this->addToAssertionCount(1);
                }
            }
        }
    }

    // get(TemporalFieldInterface)
    public function testBaseGetTemporalFieldSupported() : void
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->validFields() as $field) {
                try {
                    $sample->get($field);
                    $this->addToAssertionCount(1);
                } catch (ExceptionInterface $e) {
                    self::fail('Failed on ' . $sample . ' ' . $field);
                }
            }
        }
    }

    public function testBaseGetTemporalFieldUnsupported() : void
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->invalidFields() as $field) {
                try {
                    $sample->get($field);
                    self::fail('Failed on ' . $sample . ' ' . $field);
                } catch (ExceptionInterface $e) {
                    $this->addToAssertionCount(1);
                }
            }
        }
    }

    public function testBaseGetTemporalFieldInvalidField() : void
    {
        $this->expectException(ExceptionInterface::class);

        foreach ($this->samples() as $sample) {
            $sample->get(new MockFieldNoValue());
        }
    }

    // getInt(TemporalFieldInterface)
    public function testBaseGetIntTemporalFieldSupported() : void
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->validFields() as $field) {
                try {
                    $sample->getInt($field);
                    $this->addToAssertionCount(1);
                } catch (ExceptionInterface $e) {
                    self::fail('Failed on ' . $sample . ' ' . $field);
                }
            }
        }
    }

    public function testBaseGetIntTemporalFieldUnsupported() : void
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->invalidFields() as $field) {
                try {
                    $sample->getInt($field);
                    self::fail('Failed on ' . $sample . ' ' . $field);
                } catch (ExceptionInterface $e) {
                    $this->addToAssertionCount(1);
                }
            }
        }
    }

    public function testBaseGetIntTemporalFieldInvalidField() : void
    {
        $this->expectException(ExceptionInterface::class);

        foreach ($this->samples() as $sample) {
            $sample->getInt(new MockFieldNoValue());
        }
    }

    // testQuery(TemporalAccessorInterface)
    public function testBaseQuery() : void
    {
        foreach ($this->samples() as $sample) {
            self::assertSame('foo', $sample->query(new class implements TemporalQueryInterface
            {
                public function queryFrom(TemporalAccessorInterface $temporal) : string
                {
                    return 'foo';
                }
            }));
        }
    }
}
