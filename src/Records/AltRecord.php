<?php

namespace LabelTools\PhpCwrExporter\Records;


class AltRecord  extends Record
{
    public function __construct(
        protected string $alternateTitle,
        protected string $titleType,
        protected ?string $languageCode = ''
    ) {}

    protected function validateBeforeToString(): void
    {

    }

}
