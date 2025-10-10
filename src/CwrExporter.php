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
        $this->options['group_count'] = 1; // single group per file
        $this->options['transaction_count'] = count($works); // one transaction per work
        $this->options['header_count'] = 2; // file header + group header

        // Retrieve pre-formatted lines from the version implementation
        $headerLines = $this->version->renderHeader($this->options);
        foreach ($headerLines as $line) {
            fwrite($stream, $line . "\r\n");
        }

        $detailLines = $this->version->renderDetailLines($works, $this->options);
        $detailCount = 0;
        foreach ($detailLines as $line) {
            if ($line) { // renderDetailLines can yield null for skipped works
                fwrite($stream, $line . "\r\n");
                $detailCount++;
            }
        }

        $this->options['detail_count'] = $detailCount;

        $trailerLines = $this->version->renderTrailer($this->options);
        foreach ($trailerLines as $line) {
            fwrite($stream, $line . "\r\n");
        }
    }
}