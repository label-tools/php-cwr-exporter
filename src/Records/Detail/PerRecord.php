<?php
namespace LabelTools\PhpCwrExporter\Records\Detail;

use LabelTools\PhpCwrExporter\Records\Record;

class PerRecord extends Record
{
    protected static string $recordType = 'PER';

    protected string $stringFormat =
        "%-19s" .  // Record Prefix
        "%-45s" .  // Performing Artist Last Name
        "%-30s" .  // Performing Artist First Name
        "%-11s" .  // Performing Artist IPI Name Number
        "%-13s";   // Performing Artist IPI Base Number

    protected const IDX_LAST_NAME = 2;
    protected const IDX_FIRST_NAME = 3;
    protected const IDX_IPI_NAME = 4;
    protected const IDX_IPI_BASE = 5;

    public function __construct(
        string $lastName,
        ?string $firstName = '',
        ?string $ipiNameNumber = '',
        ?string $ipiBaseNumber = ''
    ) {
        parent::__construct();

        $this->setPerformingArtistLastName($lastName)
            ->setPerformingArtistFirstName($firstName)
            ->setPerformingArtistIpiNameNumber($ipiNameNumber)
            ->setPerformingArtistIpiBaseNumber($ipiBaseNumber);
    }

    public function setPerformingArtistLastName(string $lastName): self
    {
        if (trim($lastName) === '') {
            throw new \InvalidArgumentException('Performing Artist Last Name is required for PER records.');
        }

        return $this->setAlphaNumeric(static::IDX_LAST_NAME, $lastName, 'Performing Artist Last Name');
    }

    public function setPerformingArtistFirstName(?string $firstName): self
    {
        if ($firstName === null) {
            $this->data[static::IDX_FIRST_NAME] = '';
            return $this;
        }
        return $this->setAlphaNumeric(static::IDX_FIRST_NAME, $firstName, 'Performing Artist First Name');
    }

    public function setPerformingArtistIpiNameNumber(?string $ipi): self
    {
        if ($ipi === null) {
            $this->data[static::IDX_IPI_NAME] = '';
            return $this;
        }
        $value = trim($ipi);
        if ($value !== '' && !preg_match('/^[0-9]{11}$/', $value)) {
            throw new \InvalidArgumentException('Performing Artist IPI Name Number must be 11 digits.');
        }
        $this->data[static::IDX_IPI_NAME] = $value;
        return $this;
    }

    public function setPerformingArtistIpiBaseNumber(?string $base): self
    {
        if ($base === null) {
            $this->data[static::IDX_IPI_BASE] = '';
            return $this;
        }
        $value = trim($base);
        if ($value !== '' && !preg_match('/^[A-Z0-9]{1,13}$/', $value)) {
            throw new \InvalidArgumentException('Performing Artist IPI Base Number must be 1-13 alphanumeric characters.');
        }
        $this->data[static::IDX_IPI_BASE] = $value;
        return $this;
    }
}
