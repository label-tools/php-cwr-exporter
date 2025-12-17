<?php
namespace LabelTools\PhpCwrExporter\Definitions;

use InvalidArgumentException;

class PerformingArtistDefinition
{
    public function __construct(
        public readonly string $lastName,
        public readonly ?string $firstName = null,
        public readonly ?string $ipiNameNumber = null,
        public readonly ?string $ipiBaseNumber = null,
    ) {
        if (trim($this->lastName) === '') {
            throw new InvalidArgumentException('Performing artist last name is required.');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            lastName: $data['last_name'] ?? '',
            firstName: $data['first_name'] ?? null,
            ipiNameNumber: $data['ipi_name_number'] ?? null,
            ipiBaseNumber: $data['ipi_base_number'] ?? null,
        );
    }
}
