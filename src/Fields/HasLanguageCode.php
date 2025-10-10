<?php

namespace LabelTools\PhpCwrExporter\Fields;

use LabelTools\PhpCwrExporter\Enums\LanguageCode;

trait HasLanguageCode
{
    public function setLanguageCode(null|string|LanguageCode $languageCode): self
    {
        return $this->setEnumValue(static::getIdxFromString('IDX_LANG'), LanguageCode::class, $languageCode, 'Language Code', false);
    }

    protected function getLanguageCode(): ?LanguageCode
    {
        $code = $this->data[static::getIdxFromString('IDX_LANG')] ?? '';
        return $code === '' ? null : LanguageCode::from($code);
    }
}