<?php

namespace LabelTools\PhpCwrExporter\Fields;

use LabelTools\PhpCwrExporter\Enums\LanguageCode;

trait HasLanguageCode
{

    public function setLanguageCode(LanguageCode|string|null $languageCode): self
    {
        $data = $this->validateLanguageCode($languageCode);
        $this->data[$this->getLanguageCodeIndex()] = $data?->value ?? '';
        return $this;
    }

    protected function validateLanguageCode(LanguageCode|string|null $languageCode): ?LanguageCode
    {
        if (empty($languageCode)) {
            return null;
        }

        try {
            return $languageCode instanceof LanguageCode ? $languageCode : LanguageCode::from($languageCode);
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException(
                sprintf('Language Code "%s" is not a valid value.', $languageCode),
                0,
                $e
            );
        }
    }

    protected function getLanguageCode(): ?LanguageCode
    {
        $code = $this->data[$this->getLanguageCodeIndex()] ?? '';
        return $code === '' ? null : LanguageCode::from($code);
    }

    abstract protected function getLanguageCodeIndex(): int;
}