<?php

namespace LabelTools\PhpCwrExporter\Definitions;

use LabelTools\PhpCwrExporter\Enums\WriterDesignation;

class WriterDefinition
{
    public function __construct(
        public readonly string $interestedPartyNumber,
        public readonly string $writerFirstName,
        public readonly string $writerLastName,
        public readonly WriterDesignation|string $writerDesignationCode,
        public readonly array $territories = []
    ) {
        if ($this->interestedPartyNumber === '') {
            throw new \InvalidArgumentException('Interested Party Number is required.');
        }
        if ($this->writerFirstName === '' || $this->writerLastName === '') {
            throw new \InvalidArgumentException('Writer first and last name are required.');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            interestedPartyNumber: $data['interestedPartyNumber'] ?? throw new \InvalidArgumentException('interestedPartyNumber missing'),
            writerFirstName: $data['writerFirstName'] ?? throw new \InvalidArgumentException('writerFirstName missing'),
            writerLastName: $data['writerLastName'] ?? throw new \InvalidArgumentException('writerLastName missing'),
            writerDesignationCode: $data['writerDesignationCode'] instanceof WriterDesignation
                ? $data['writerDesignationCode']
                : WriterDesignation::from($data['writerDesignationCode']),
            territories: $data['territories'] ?? []
        );
    }
}