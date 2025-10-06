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
        public readonly ?string $ipiNameNumber = null,
        public readonly ?string $prAffiliationSociety = null,
        public readonly array $territories = []
    ) {
        if ($this->interestedPartyNumber === '') {
            throw new \InvalidArgumentException('Interested Party Number is required.');
        }

        if ($this->writerDesignationCode instanceof WriterDesignation === false) {
            // allow plain string values too; they will be normalized in fromArray
        }
    }

    public static function fromArray(array $data): self
    {
        $interestedPartyNumber = $data['interested_party_number'] ?? throw new \InvalidArgumentException('interested_party_number missing');

        $firstName = $data['first_name'] ?? '';

        $lastName = $data['last_name'] ?? throw new \InvalidArgumentException('last_name missing');

        // designation can arrive as enum value or raw string
        $designationRaw = $data['designation_code'] ?? throw new \InvalidArgumentException('designation_code missing');
        $designation = $designationRaw instanceof WriterDesignation ? $designationRaw : WriterDesignation::from($designationRaw);

        $ipiNameNumber = $data['ipi_name_number'] ?? null;
        $prAffiliationSociety = $data['pr_affiliation_society'] ?? null;
        $territories = $data['territories'] ?? [];

        return new self(
            interestedPartyNumber: $interestedPartyNumber,
            writerFirstName: $firstName,
            writerLastName: $lastName,
            writerDesignationCode: $designation,
            ipiNameNumber: $ipiNameNumber,
            prAffiliationSociety: $prAffiliationSociety,
            territories: $territories,
        );
    }
}