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
        public readonly array $territories = [],
        public readonly null|int|string $publisherInterestedPartyNumber = null, //this was added so we know how to link writers to publishers
        public readonly bool $controlled = true,
        public readonly int|float|null $prOwnershipShare = 0,
        public readonly null|int|string $mrAffiliationSociety = null,
        public readonly int|float|null $mrOwnershipShare = 0,
        public readonly null|int|string $srAffiliationSociety = null,
        public readonly int|float|null $srOwnershipShare = 0,
        public readonly string $reversionaryIndicator = '',
        public readonly string $firstRecordingRefusalIndicator = '',
        public readonly string $workForHireIndicator = '',
        public readonly string $writerIpiBaseNumber = '',
        public readonly string $personalNumber = '',
        public readonly string $usaLicenseIndicator = '',
        public readonly ?string $taxId = null,

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
        $publisherInterestedPartyNumber = $data['publisher_interested_party_number'] ?? null;
        $controlled = static::normalizeBool($data['controlled'] ?? true);

        return new self(
            interestedPartyNumber: $interestedPartyNumber,
            writerFirstName: $firstName,
            writerLastName: $lastName,
            writerDesignationCode: $designation,
            ipiNameNumber: $ipiNameNumber,
            prAffiliationSociety: $prAffiliationSociety,
            territories: $territories,
            publisherInterestedPartyNumber: $publisherInterestedPartyNumber,
            controlled: $controlled,
            prOwnershipShare: $data['pr_ownership_share'] ?? 0,
            mrAffiliationSociety: $data['mr_affiliation_society'] ?? null,
            mrOwnershipShare: $data['mr_ownership_share'] ?? 0,
            srAffiliationSociety: $data['sr_affiliation_society'] ?? null,
            srOwnershipShare: $data['sr_ownership_share'] ?? 0,
            reversionaryIndicator: $data['reversionary_indicator'] ?? '',
            firstRecordingRefusalIndicator: $data['first_recording_refusal_indicator'] ?? '',
            workForHireIndicator: $data['work_for_hire_indicator'] ?? '',
            writerIpiBaseNumber: $data['writer_ipi_base_number'] ?? '',
            personalNumber: $data['personal_number'] ?? '',
            usaLicenseIndicator: $data['usa_license_indicator'] ?? '',
            taxId: $data['tax_id'] ?? null,
        );
    }

    private static function normalizeBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $value = strtoupper((string)$value);
        return in_array($value, ['1', 'Y', 'YES', 'TRUE'], true);
    }
}
