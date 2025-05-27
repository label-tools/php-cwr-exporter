<?php

namespace LabelTools\PhpCwrExporter\Version\V22;

use LabelTools\PhpCwrExporter\Contracts\VersionInterface;
use LabelTools\PhpCwrExporter\Version\V22\Records\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrhRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrtRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\TrlRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SpuRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SptRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\NwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\OwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\PwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\AltRecord;

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

        // Transmission Header
        $records[] = new HdrRecord(
            senderType: $options['sender_type'] ?? config('cwr.sender_type'),
            senderId: $options['sender_id'] ?? config('cwr.sender_id'),
            senderName: $options['sender_name'] ?? config('cwr.sender_name'),
            creationDate: $options['creation_date'] ?? null,
            creationTime: $options['creation_time'] ?? null,
            transmissionDate: $options['transmission_date'] ?? null,
            characterSet: $options['character_set'] ?? config('cwr.character_set', ''),
        );

        // Single group header
        $records[] = new GrhRecord('NWR', 1);

        // Detail records
        $detailCount = 0;
        foreach ($works as $index => $work) {
            $transactionSeq = $index + 1;
            $workRecs = $this->buildWorkRecords($work, $transactionSeq);
            foreach ($workRecs as $rec) {
                $records[] = $rec;
            }
            $detailCount += count($workRecs);
        }

        // Group trailer (includes GRH & GRT)
        $records[] = new GrtRecord(1, count($works), $detailCount + 2);

        // Transmission trailer
        $groupCount      = 1;
        $transactionCount = count($works);
        $totalRecords    = count($records) + 1;
        $records[] = new TrlRecord($groupCount, $transactionCount, $totalRecords);

        return $records;
    }

    public function buildWorkRecords($work, int $transactionSequence): array
    {
        $records = [];

        // 1) SPU / SPT - publisher chain
        if (!empty($work['publishers'])) {
            foreach ($work['publishers'] as $pub) {
                $records[] = new SpuRecord(
                    $pub['sequence'],                        // Publisher Sequence #
                    $pub['interested_party_number'],         // Interested Party #
                    $pub['name'],                            // Publisher Name
                    $pub['type'],                            // Publisher Type
                    $pub['tax_id']         ?? null,          // Tax ID
                    $pub['ipi_name_number'],                 // IPI Name #
                    $pub['submitter_agreement_number'] ?? null,
                    $pub['society_agreement_number']   ?? null,
                    $pub['pr_affiliation_society']      ?? null,
                    $pub['pr_affiliation_share']        ?? 0,
                    $pub['mr_affiliation_society']      ?? null,
                    $pub['mr_affiliation_share']        ?? 0
                );
                // Territories
                if (!empty($pub['territories'])) {
                    $seq = 0;
                    foreach ($pub['territories'] as $terr) {
                        $seq++;
                        $records[] = new SptRecord(
                            $pub['interested_party_number'],    // Interested Party #
                            $terr['pr_share'],                  // PR Collection Share
                            $terr['mr_share'],                  // MR Collection Share
                            $terr['sr_share'],                  // SR Collection Share
                            $terr['territory_code'],            // Territory Code
                            $terr['inclusion_exclusion'],       // Inclusion/Exclusion
                            $terr['shares_change_flag'] ?? 'N',  // Shares Change Flag
                            $seq                                 // Sequence #
                        );
                    }
                }
            }
        }

        // 2) NWR header
        $records[] = new NwrRecord(
            $work['title'],
            $work['language']                ?? null,
            $work['submitter_work_number'],
            $work['iswc']                    ?? null,
            $work['copyright_date']          ?? null,
            $work['copyright_number']        ?? null,
            $work['distribution_category'],
            $work['duration']                ?? null,
            $work['recorded']                ?? false,
            $work['text_music_relationship'] ?? ''
        );

        // 3) SWR / PWR - writers
        if (!empty($work['writers'])) {
            foreach ($work['writers'] as $writer) {
                // SWR
                $records[] = new SwrRecord(
                    'SWR',
                    $writer['interested_party_number'],
                    $writer['last_name'],
                    $writer['first_name']          ?? null,
                    $writer['unknown_indicator']   ?? false,
                    $writer['designation_code']    ?? '',
                    $writer['tax_id']              ?? null,
                    $writer['ipi_name_number']     ?? null,
                    $writer['pr_affiliation_society'] ?? null,
                    $writer['pr_ownership_share']  ?? 0
                );
                // PWR for each publisher under writer
                if (!empty($writer['publishers'])) {
                    foreach ($writer['publishers'] as $pub) {
                        $records[] = new PwrRecord(
                            $pub['publisher_ip'],
                            $pub['publisher_name']       ?? '',
                            $pub['submitter_agreement_number'] ?? null,
                            $pub['society_agreement_number']   ?? null,
                            $writer['interested_party_number'],
                            $pub['publisher_sequence']
                        );
                    }
                }
            }
        }

        // 4) OWR - other writers
        if (!empty($work['other_writers'])) {
            foreach ($work['other_writers'] as $writer) {
                $records[] = new OwrRecord(
                    $writer['interested_party_number'],
                    $writer['last_name'],
                    $writer['first_name']        ?? null,
                    $writer['unknown_indicator'] ?? false,
                    $writer['designation_code']  ?? '',
                    $writer['tax_id']            ?? null,
                    $writer['ipi_name_number']   ?? null,
                    $writer['pr_affiliation_society'] ?? null,
                    $writer['pr_ownership_share'] ?? 0
                );
            }
        }

        // 5) ALT - alternate titles
        if (!empty($work['alternate_titles'])) {
            foreach ($work['alternate_titles'] as $alt) {
                $records[] = new AltRecord(
                    $alt['title'],
                    $alt['type'],
                    $alt['language'] ?? ''
                );
            }
        }

        return $records;
    }
}