<?php

namespace LabelTools\PhpCwrExporter\Fields;

use LabelTools\PhpCwrExporter\Enums\TisCode;

trait HasTisNumericCode
{
    public function setTisNumericCode(string|TisCode $code): self
    {
        return $this->setEnumValue(static::getIdxFromString('IDX_TIS_NUMERIC_CODE'), TisCode::class, $code, 'TIS Numeric Code');
    }
}