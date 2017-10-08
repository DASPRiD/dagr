<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

interface TemporalInterface extends TemporalAccessorInterface
{
    public function isSupportedUnit(TemporalUnitInterface $unit) : bool;

    public function with(TemporalAdjusterInterface $adjuster) : self;

    public function withField(TemporalFieldInterface $field, int $newValue) : self;

    public function plusAmount(TemporalAmountInterface $amountToAdd) : self;

    public function plus(int $amountToAdd, TemporalUnitInterface $unit) : self;

    public function minusAmount(TemporalAmountInterface $amountToSubtract) : self;

    public function minus(int $amountToSubtract, TemporalUnitInterface $unit) : self;

    public function until(TemporalInterface $endExclusive, TemporalUnitInterface $unit) : int;
}
