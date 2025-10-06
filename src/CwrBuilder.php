<?php
namespace LabelTools\PhpCwrExporter;

use LabelTools\PhpCwrExporter\CwrExporter;
use LabelTools\PhpCwrExporter\Definitions\PublisherDefinition;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Enums\SenderType;
use LabelTools\PhpCwrExporter\Version\V22\Version as V22Version;

class CwrBuilder
{
    protected CwrExporter $exporter;
    protected array $works = [];
    protected array $options = [];

    private function __construct(string $version)
    {
        // right now we only have 2.2; you could map '2.1' → another impl
        $versionImpl = match ($version) {
            '2.2' => new V22Version(),
            default => throw new \InvalidArgumentException("Unsupported CWR version: {$version}"),
        };

        $this->options['version'] = $version;
        $this->exporter = new CwrExporter($versionImpl);
    }

    public static function make($version): self
    {
        return new self($version);
    }

    public static function v22(): self
    {
        return new self('2.2');
    }

    public function senderType(SenderType|string $type): self
    {
        if (is_string($type)) {
            $type = SenderType::from($type);
        }

        $this->options['sender_type'] = $type->value;
        return $this;
    }

    public function senderId(string $id): self
    {
        if (!isset($this->options['sender_type'])) {
            throw new \InvalidArgumentException("senderType must be set before senderId.");
        }
        $type = $this->options['sender_type'];

        if (in_array($type, ['PB', 'AA', 'WR'], true)) {
                if (!preg_match('/^[0-9]{9,}$/', $id)) {
                    throw new \InvalidArgumentException("For senderType $type, senderId must be a numeric IPI number of at least 9 digits.");
                }
        } elseif ($type === 'SO') {
            if (!preg_match('/^[A-Z0-9]{3,5}$/', $id)) {
                throw new \InvalidArgumentException("For senderType SO, senderId must be a valid society code (3-5 alphanumeric).");
            }
        } else {
            throw new \InvalidArgumentException("Invalid senderType: $type");
        }
        $this->options['sender_id'] = $id;
        return $this;
    }

    public function senderName(string $name): self
    {
        if (strlen($name) > 45) {
            throw new \InvalidArgumentException("senderName must not exceed 45 characters.");
        }
        $this->options['sender_name'] = $name;
        return $this;
    }

    public function characterSet(string $cs): self
    {
        if (strlen($cs) > 15) {
            throw new \InvalidArgumentException("characterSet must not exceed 15 characters.");
        }
        $this->options['character_set'] = $cs;
        return $this;
    }

    public function revision(string $rev): self
    {
        if (!preg_match('/^\d{1,3}$/', $rev)) {
            throw new \InvalidArgumentException("revision must be 1 to 3 digits.");
        }
        $this->options['revision'] = $rev;
        return $this;
    }

    public function software(string $package, string $version): self
    {
        if (strlen($package) > 30) {
            throw new \InvalidArgumentException("software_package must not exceed 30 characters.");
        }
        if (strlen($version) > 30) {
            throw new \InvalidArgumentException("software_version must not exceed 30 characters.");
        }
        $this->options['software_package'] = $package;
        $this->options['software_version'] = $version;
        return $this;
    }

    public function transaction(string $code): self
    {
        $allowed = ['NWR', 'REV', 'DEL', 'REC'];
        if (!in_array($code, $allowed, true)) {
            throw new \InvalidArgumentException("transaction_type must be one of: " . implode(', ', $allowed));
        }
        $this->options['transaction_type'] = $code;
        return $this;
    }

    public function works(array $works): self
    {
        if (empty($works)) {
            throw new \InvalidArgumentException("works must be a non-empty array.");
        }

        $this->works = array_map(
            function ($w) {
                if (!$w instanceof WorkDefinition) {
                    $w = WorkDefinition::fromArray($w);
                }
                return $w;
            },
            $works
        );

        return $this;
    }

    public function addWork(array $work): self
    {
        $this->works[] = $work;
        return $this;
    }

    public function export(): string
    {
        // single group per file
        $this->options['group_count'] = 1;
        // one transaction per work
        $this->options['transaction_count'] = count($this->works);
        // headers: file header + group header
        $this->options['header_count'] = 2;

        // detail records: NWR per work, SWR/SWT for writers, SPU/SPT for publishers, and minimal PWR linking
        $detailCount = 0;
        foreach ($this->works as $work) {
            // NWR per work
            $detailCount += 1;

            // Writers: one SWR per writer; one SWT per writer territory
            $writerCount = 0;
            $writerTerritoryCount = 0;
            if (!empty($work->writers) && is_array($work->writers)) {
                $writerCount = count($work->writers);
                $detailCount += $writerCount; // SWR records
                foreach ($work->writers as $wr) {
                    $writerTerritoryCount += count($wr->territories ?? []); // SWT
                }
                $detailCount += $writerTerritoryCount;
            }

            // Publishers: one SPU per publisher; one SPT per publisher territory
            $publisherCount = 0;
            $publisherTerritoryCount = 0;
            if (!empty($work->publishers) && is_array($work->publishers)) {
                $publisherCount = count($work->publishers);
                $detailCount += $publisherCount; // SPU records
                foreach ($work->publishers as $pub) {
                    $publisherTerritoryCount += count($pub->territories ?? []); // SPT
                }
                $detailCount += $publisherTerritoryCount;
            }

            // Minimal PWR linking: at least one PWR per writer when there is at least one publisher
            // Note: If your exporter emits multiple PWR per writer (e.g., one per publisher),
            // replace the next line with `$detailCount += ($writerCount * $publisherCount);`
            if ($writerCount > 0 && $publisherCount > 0) {
                $detailCount += $writerCount; // PWR
            }
        }

        $this->options['detail_count'] = $detailCount;
        return $this->exporter->export($this->works, $this->options);
    }
}