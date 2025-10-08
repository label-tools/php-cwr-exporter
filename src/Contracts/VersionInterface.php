<?php

namespace LabelTools\PhpCwrExporter\Contracts;

interface VersionInterface
{
    public function renderHeader(array $opts): array;
    public function renderDetailLines(array $works, array $opts): array;
    public function renderTrailer(array $opts): array;
    public function getVersionNumber(): string;
    public function getRevision(): string;

}