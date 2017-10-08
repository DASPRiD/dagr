<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

interface TemporalAccessorInterface
{
    public function isSupportedField(TemporalFieldInterface $field) : bool;

    public function range(TemporalFieldInterface $field) : ValueRange;

    public function get(TemporalFieldInterface $field) : int;

    public function getInt(TemporalFieldInterface $field) : int;

    /**
     * @return mixed
     */
    public function query(TemporalQueryInterface $query);

    public function __toString() : string;
}
