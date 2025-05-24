<?php

namespace LabelTools\PhpCwrExporter\Version\V22;

use LabelTools\PhpCwrExporter\Contracts\VersionInterface;
use LabelTools\PhpCwrExporter\Version\V22\Records\AltRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrhRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrtRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\TrlRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\NwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\OwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\PwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SptRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SpuRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SwrRecord;

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

        // 2) Publisher chain for submitter-controlled publishers
        foreach ($work['publishers'] as $pubChain) {
            // a) SPU record
            $records[] = new SpuRecord(
                $pubChain['sequence'],
                $pubChain['interested_party_number'],
                $pubChain['name'],
                $pubChain['type'],
                $pubChain['tax_id'] ?? null,
                $pubChain['ipi_name_number'],
                $pubChain['submitter_agreement_number'] ?? null,
                $pubChain['society_agreement_number'] ?? null,
                $pubChain['pr_affiliation_society'] ?? null,
                $pubChain['pr_ownership_share'],
                $pubChain['mr_affiliation_society'] ?? null,
                $pubChain['mr_ownership_share']
            );

            // b) One or more SPT territory records
            $seq = 0;
            foreach ($pubChain['territories'] as $terr) {
                $seq++;
                $records[] = new SptRecord(
                    $pubChain['interested_party_number'],
                    $terr['pr_share'],
                    $terr['mr_share'],
                    $terr['sr_share'],
                    $terr['territory_code'],
                    $terr['inclusion_exclusion'],
                    $terr['shares_change_flag'] ?? 'N',
                    $seq
                );
            }
        }

        foreach ($work['writers'] as $writer) {
            $records[] = new SwrRecord(
                'SWR',
                $writer['interested_party_number'],
                $writer['last_name'],
                $writer['first_name'] ?? null,
                $writer['unknown_indicator'] ?? false,
                $writer['designation_code'] ?? '',
                $writer['tax_id'] ?? null,
                $writer['ipi_name_number'] ?? null,
                $writer['pr_affiliation_society'] ?? null,
                $writer['pr_ownership_share'] ?? 0
            );
        }

                // 3) OWR lines for other writers, if any
        if (!empty($work['other_writers'])) {
            foreach ($work['other_writers'] as $writer) {
                $records[] = new OwrRecord(
                    $writer['interested_party_number'],
                    $writer['last_name'],
                    $writer['first_name'] ?? null,
                    $writer['unknown_indicator'] ?? false,
                    $writer['designation_code'] ?? '',
                    $writer['tax_id'] ?? null,
                    $writer['ipi_name_number'] ?? null,
                    $writer['pr_affiliation_society'] ?? null,
                    $writer['pr_ownership_share'] ?? 0
                );
            }
        }

        // 4) PWR lines for each publisher of this writer
        if (!empty($writer['publishers'])) {
            foreach ($writer['publishers'] as $pub) {
                $records[] = new PwrRecord(
                    $pub['publisher_ip'],
                    $pub['publisher_name'] ?? '',
                    $pub['submitter_agreement_number'] ?? null,
                    $pub['society_agreement_number'] ?? null,
                    $writer['interested_party_number'],   // link back to SWR/OWR
                    $pub['publisher_sequence']
                );
            }
        }


        if (!empty($work['alternate_titles'])) {
            foreach ($work['alternate_titles'] as $alt) {
                $records[] = new AltRecord(
                    $alt['title'],
                    $alt['type'],
                    $alt['language']  // must be provided when type is “OL” or “AL”
                );
            }
        }


        return $records;
    }
}