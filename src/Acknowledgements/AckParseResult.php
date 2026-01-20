<?php

namespace LabelTools\PhpCwrExporter\Acknowledgements;

final class AckParseResult implements \JsonSerializable
{
    /**
     * @param array $file
     * @param array $groups
     */
    public function __construct(
        public readonly array $file,
        public readonly array $groups,
    ) {
    }

    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'groups' => $this->groups,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
