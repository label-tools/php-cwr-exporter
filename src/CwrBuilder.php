<?php
namespace LabelTools\PhpCwrExporter;

use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Version\V22\Version as V22Version;
use LabelTools\PhpCwrExporter\Enums\SenderType;

class CwrBuilder
{
    protected CwrExporter $exporter;
    protected array $works = [];

    private function __construct(CwrExporter $exporter)
    {
        $this->exporter = $exporter;
    }

    public static function make(CwrExporter $exporter): self
    {
        return new self($exporter);
    }

    public static function v22(): self
    {
        $exporter = new CwrExporter(new V22Version());
        return new self($exporter);
    }

    public function senderType(SenderType|string $type): self
    {
        if ($type instanceof SenderType) {
            $type = $type->value;
        }
        $this->exporter->setOption('sender_type', $type);
        return $this;
    }

    public function transaction(string $code): self
    {
        $allowed = ['NWR', 'REV', 'DEL', 'REC'];
        if (!in_array($code, $allowed, true)) {
            throw new \InvalidArgumentException("Transaction type must be one of: " . implode(', ', $allowed));
        }
        $this->exporter->setOption('transaction_type', $code);
        return $this;
    }

    public function senderId(string $id): self
    {
        $this->exporter->setOption('sender_id', $id);
        return $this;
    }

    public function senderName(string $name): self
    {
        $this->exporter->setOption('sender_name', $name);
        return $this;
    }

    public function software(string $package, string $version): self
    {
        $this->exporter->setOption('software_package', $package);
        $this->exporter->setOption('software_version', $version);
        return $this;
    }

    public function characterSet(string $cs): self
    {
        $this->exporter->setOption('character_set', $cs);
        return $this;
    }

    public function revision(string $rev): self
    {
        $this->exporter->setOption('revision', $rev);
        return $this;
    }

    public function works(array $works): self
    {
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

    public function addWork(array|WorkDefinition $work): self
    {
        if (!$work instanceof WorkDefinition) {
            $work = WorkDefinition::fromArray($work);
        }
        $this->works[] = $work;
        return $this;
    }

    public function export(): string
    {
        if (empty($this->works)) {
            throw new \LogicException("Cannot export without works. Please add works using works() or addWork().");
        }
        return $this->exporter->export($this->works);
    }

    /**
     * Exports the CWR data to a stream resource (e.g., a file handle).
     * This is highly recommended for large datasets to conserve memory.
     *
     * @param resource $stream A valid stream resource, typically created with fopen().
     * @return void
     */
    public function exportToStream($stream): void
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new \InvalidArgumentException('A valid stream resource must be provided.');
        }
        if (empty($this->works)) {
            throw new \LogicException("Cannot export without works. Please add works using works() or addWork().");
        }
        $this->exporter->exportToStream($this->works, $stream);
    }

    public function getWorks(): array
    {
        return $this->works;
    }
}