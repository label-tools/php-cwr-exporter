<?php

namespace LabelTools\PhpCwrExporter;

use LabelTools\PhpCwrExporter\Contracts\VersionInterface;

class CwrExporter
{
    protected array $options = [];
    private VersionInterface $version;

    public function __construct(VersionInterface $version)
    {
        $this->version = $version;
    }

    public function setOption(string $key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function getVersion(): VersionInterface
    {
        return $this->version;
    }

    public function getSkippedWorks(): array
    {
        return method_exists($this->version, 'getSkippedWorks')
            ? $this->version->getSkippedWorks()
            : [];
    }

    public function export(array $works): string
    {
        $stream = fopen('php://memory', 'r+');
        $this->exportToStream($works, $stream);
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        return $contents;
    }

    /**
     * Exports the CWR data to the given stream resource.
     *
     * @param \LabelTools\PhpCwrExporter\Definitions\WorkDefinition[] $works
     * @param resource $stream
     */
    public function exportToStream(array $works, $stream): void
    {
        $headerLines = $this->version->renderHeader($this->options);
        $headerCount = count($headerLines);

        foreach ($headerLines as $line) {
            fwrite($stream, $line . "\r\n");
        }

        $bodyLines = $this->version->renderDetailLines($works, $this->options);
        $bodyCount = 0;
        $transactionCount = 0;
        $groupCount = 0;

        foreach ($bodyLines as $line) {
            if ($line === null) {
                continue;
            }

            fwrite($stream, $line . "\r\n");
            $bodyCount++;

            $recordType = substr($line, 0, 3);
            if (in_array($recordType, ['NWR', 'REV'], true)) {
                $transactionCount++;
            }
            if ($recordType === 'GRH') {
                $groupCount++;
            }
        }

        $this->options['header_count'] = $headerCount;
        $this->options['body_count'] = $bodyCount;
        $this->options['group_count'] = $groupCount;
        $this->options['transaction_count'] = $transactionCount;
        $this->options['record_count'] = $headerCount + $bodyCount + 1;

        $trailerLines = $this->version->renderTrailer($this->options);
        foreach ($trailerLines as $line) {
            fwrite($stream, $line . "\r\n");
        }
    }
}
