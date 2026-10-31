<?php

namespace LabelTools\PhpCwrExporter\Acknowledgements;

final class AckParseResult implements \JsonSerializable
{
    /**
     * @param array $file
     * @param array $groups
     * @param array $warnings File-level findings that did not stop parsing
     */
    public function __construct(
        public readonly array $file,
        public readonly array $groups,
        public readonly array $warnings = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'groups' => $this->groups,
            'warnings' => $this->warnings,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
