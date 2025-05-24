<?php

namespace LabelTools\PhpCwrExporter\Version\V22;

use LabelTools\PhpCwrExporter\Contracts\VersionInterface;
use LabelTools\PhpCwrExporter\Version\V22\Records\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrhRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrtRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\TrlRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\NwrRecord;

/**
 * CWR Version 2.2 implementation.
 */
class Version implements VersionInterface
{
    public function getVersion(): string
    {
        return '2.2';
    }

    public function buildControlRecords(array $works, array $options = []): array
    {
        $records = [];

        // 1) Transmission Header
        $records[] = new HdrRecord(
            $options['sender_type'] ?? config('cwr.sender_type'),
            $options['sender_id'] ?? config('cwr.sender_id'),
            $options['sender_name'] ?? config('cwr.sender_name'),
            $options['character_set'] ?? config('cwr.character_set', ''),
            $this->getVersion(),
            $options['revision'] ?? config('cwr.revision', '1'),
            $options['software_package'] ?? config('cwr.software_package', ''),
            $options['software_version'] ?? config('cwr.software_version', '')
        );

        // 2) Single group for all NWR transactions
        $transactionType = 'NWR';
        $groupId = 1;
        $records[] = new GrhRecord($transactionType, $groupId);

        // 3) Detail records for each work
        $transactionSequence = 0;
        $detailCount = 0;
        foreach ($works as $work) {
            $transactionSequence++;
            $workRecords = $this->buildWorkRecords($work, $transactionSequence);
            foreach ($workRecords as $rec) {
                $records[] = $rec;
                $detailCount++;
            }
        }

        // 4) Group Trailer
        $transactionCount = count($works);
        $groupRecordCount = $detailCount + 2; // includes GRH & GRT
        $records[] = new GrtRecord($groupId, $transactionCount, $groupRecordCount);

        // 5) Transmission Trailer
        $groupCount = 1;
        $totalRecords = count($records) + 1; // include TRL itself
        $records[] = new TrlRecord($groupCount, $transactionCount, $totalRecords);

        return $records;
    }


    public function buildWorkRecords($work, int $transactionSequence): array
    {
        $records = [];

        // 1) Build the NWR header
        $records[] = new NwrRecord(
            $work['title'],
            $work['language'] ?? null,
            $work['submitter_work_number'],
            $work['iswc'] ?? null,
            $work['copyright_date'] ?? null,
            $work['copyright_number'] ?? null,
            $work['distribution_category'],
            $work['duration'] ?? null,
            $work['recorded'] ?? false,
            $work['text_music_relationship'] ?? ''
        );

        // 2) Append other detail records (SPU, SWR, etc.)…

        return $records;
    }
}