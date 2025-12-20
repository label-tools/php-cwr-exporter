<?php
namespace LabelTools\PhpCwrExporter\Definitions;

use LabelTools\PhpCwrExporter\Enums\PublisherType;

class PublisherDefinition
{
    public function __construct(
        public readonly string $interestedPartyNumber,
        public readonly ?string $publisherName,
        public readonly PublisherType|string|null $publisherType,
        public readonly string $publisherIpiName,
        public readonly ?string $taxId = null,
        public readonly ?string $submitterAgreementNumber = null,
        public readonly ?string $prAffiliationSociety = null,
        public readonly int|float $prOwnershipShare = 0,
        public readonly int|float $prCollectionShare = 0,
        public readonly ?string $mrAffiliationSociety = null,
        public readonly int|float $mrOwnershipShare = 0,
        public readonly int|float $mrCollectionShare = 0,
        public readonly ?string $srAffiliationSociety = null,
        public readonly int|float $srOwnershipShare = 0,
        public readonly bool $controlled = true,
        public readonly int|float $srCollectionShare = 0,
        public readonly array $territories = [],
        public readonly bool $publisherUnknownIndicator = false,
    ) {
        if ($this->controlled) {
            $errors = [];
            if (empty($this->publisherName)) {
                $errors[] = 'publisherName';
            }
            if (empty($this->publisherType)) {
                $errors[] = 'publisherType';
            }
            if (empty($this->publisherIpiName)) {
                $errors[] = 'publisherIpiName';
            }
            if ($errors) {
                throw new \InvalidArgumentException('For controlled publishers, the following fields are required: ' . implode(', ', $errors));
            }
        }
    }

    public static function fromArray(array $data): self
    {
        $controlled = static::normalizeBool($data['controlled'] ?? true);
        $name = $data['name'] ?? '';
        $publisherTypeRaw = $data['type'] ?? null;
        $ipiName = $data['ipi_name_number'] ?? '';

        if ($controlled) {
            $errors = [];
            if (empty($name)) {
                $errors[] = 'name';
            }
            if (empty($publisherTypeRaw)) {
                $errors[] = 'type';
            }
            if (empty($ipiName)) {
                $errors[] = 'ipi_name_number';
            }
            if ($errors) {
                throw new \InvalidArgumentException('For controlled publishers, the following fields are required: ' . implode(', ', $errors));
            }
        }

        $publisherType = null;
        if ($publisherTypeRaw instanceof PublisherType) {
            $publisherType = $publisherTypeRaw;
        } elseif (!empty($publisherTypeRaw)) {
            try {
                $publisherType = PublisherType::from($publisherTypeRaw);
            } catch (\ValueError $e) {
                if ($controlled) {
                    throw $e;
                }
            }
        }

        if (!$controlled && $publisherType === null) {
            $publisherType = PublisherType::ORIGINAL_PUBLISHER;
        }

        $interestedPartyNumber = $data['interested_party_number'] ?? '';
        if ($controlled && $interestedPartyNumber === '') {
            throw new \InvalidArgumentException('interested_party_number missing');
        }

        return new self(
            interestedPartyNumber: (string) $interestedPartyNumber,
            publisherName: $name,
            publisherType: $publisherType,
            publisherIpiName: $ipiName,
            taxId: $data['tax_id'] ?? null,
            submitterAgreementNumber: $data['submitter_agreement_number'] ?? null,
            prAffiliationSociety: $data['pr_affiliation_society'] ?? null,
            prOwnershipShare: $data['pr_ownership_share'] ?? 0,
            prCollectionShare: $data['pr_collection_share'] ?? 0,
            mrAffiliationSociety: $data['mr_affiliation_society'] ?? null,
            mrOwnershipShare: $data['mr_ownership_share'] ?? 0,
            mrCollectionShare: $data['mr_collection_share'] ?? 0,
            srAffiliationSociety: $data['sr_affiliation_society'] ?? null,
            srOwnershipShare: $data['sr_ownership_share'] ?? 0,
            controlled: $controlled,
            srCollectionShare: $data['sr_collection_share'] ?? 0,
            territories: $data['territories'] ?? [],
            publisherUnknownIndicator: static::normalizeBool($data['publisher_unknown_indicator'] ?? false),
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
