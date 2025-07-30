<?php
namespace LabelTools\PhpCwrExporter\Definitions;

use LabelTools\PhpCwrExporter\Enums\PublisherType;

class PublisherDefinition
{
    public function __construct(
        public readonly string $interestedPartyNumber,
        public readonly string $publisherName,
        public readonly PublisherType|string $publisherType,
        public readonly string $publisherIpiName,
        public readonly ?string $taxId = null,
        public readonly ?string $submitterAgreementNumber = null,
        public readonly ?string $prAffiliationSociety = null,
        public readonly int $prOwnershipShare = 0,
        public readonly ?string $mrAffiliationSociety = null,
        public readonly int $mrOwnershipShare = 0,
        public readonly ?string $srAffiliationSociety = null,
        public readonly int $srOwnershipShare = 0,
        public readonly array $territories = []
    ) {

        if ($this->publisherName === '') {
            throw new \InvalidArgumentException('publisherName is required.');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            interestedPartyNumber: $data['interested_party_number'] ?? throw new \InvalidArgumentException('interested_party_number missing'),
            publisherName: $data['name'] ?? throw new \InvalidArgumentException('name missing'),
            publisherType: PublisherType::from($data['type']),
            publisherIpiName: $data['ipi_name_number'] ?? throw new \InvalidArgumentException('ipi_name_number missing'),
            taxId: $data['tax_id'] ?? null,
            submitterAgreementNumber: $data['submitter_agreement_number'] ?? null,
            prAffiliationSociety: $data['pr_affiliation_society'] ?? null,
            prOwnershipShare: $data['pr_ownership_share'] ?? 0,
            mrAffiliationSociety: $data['mr_affiliation_society'] ?? null,
            mrOwnershipShare: $data['mr_ownership_share'] ?? 0,
            srAffiliationSociety: $data['sr_affiliation_society'] ?? null,
            srOwnershipShare: $data['sr_ownership_share'] ?? 0,
            territories: $data['territories'] ?? []
        );
    }
}