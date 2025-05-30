<?php

namespace LabelTools\PhpCwrExporter\Version\V22\Records;

use LabelTools\PhpCwrExporter\Version\V21\Records\HdrRecord as V21HdrRecord;

class HdrRecord extends V21HdrRecord
{
    private const INDEX_VERSION = 10;
    private const INDEX_REVISION = 11;
    private const INDEX_SW_PACKAGE = 12;
    private const INDEX_SW_PACKAGE_VERSION = 13;

    public function __construct(
        string $senderType,
        string $senderId,
        string $senderName,
        ?string $creationDate = null,
        ?string $creationTime = null,
        ?string $transmissionDate = null,
        ?string $characterSet = null,
        ?string $version = null,
        ?int $revision = null,
        ?string $softwarePackage = null,
        ?string $softwarePackageVersion = null
    ) {
        parent::__construct($senderType, $senderId, $senderName, $creationDate, $creationTime, $transmissionDate, $characterSet);

        $this->stringFormat .= "%-3s%-3s%-30s%-30s";

        $this->setVersion($version);
        $this->setRevision($revision);
        $this->setSoftwarePackage($softwarePackage);
        $this->setSoftwarePackageVersion($softwarePackageVersion);
    }

    public function setVersion(?string $version): self
    {
        if ($version !== null && !preg_match('/^[0-9]{1,3}\.[0-9]{1,3}$/', $version)) {
            throw new \InvalidArgumentException("Version must be in format 'X.Y' where X and Y are numbers.");
        }

        $this->data[self::INDEX_VERSION] = $version;
        return $this;
    }

    public function setRevision(?int $revision): self
    {
        if ($revision !== null && ($revision < 0 || $revision > 999)) {
            throw new \InvalidArgumentException("Revision must be a number between 0 and 999.");
        }

        $this->data[self::INDEX_REVISION] = $revision;
        return $this;
    }

    public function setSoftwarePackage(?string $softwarePackage): self
    {
        if ($softwarePackage !== null && mb_strlen($softwarePackage) > 30) {
            throw new \InvalidArgumentException("Software Package must be at most 30 characters long.");
        }
        $this->data[self::INDEX_SW_PACKAGE] = $softwarePackage;
        return $this;
    }

    public function setSoftwarePackageVersion(?string $softwarePackageVersion): self
    {
        if ($softwarePackageVersion !== null && mb_strlen($softwarePackageVersion) > 30) {
            throw new \InvalidArgumentException("Software Package Version must be at most 30 characters long.");
        }
        $this->data[self::INDEX_SW_PACKAGE_VERSION] = $softwarePackageVersion;
        return $this;
    }

}