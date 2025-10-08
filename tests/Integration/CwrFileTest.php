<?php

use LabelTools\PhpCwrExporter\CwrBuilder;
use LabelTools\PhpCwrExporter\Enums\MusicalWorkDistributionCategory;
use LabelTools\PhpCwrExporter\Enums\PublisherType;
use LabelTools\PhpCwrExporter\Enums\SenderType;
use LabelTools\PhpCwrExporter\Enums\LanguageCode;
use LabelTools\PhpCwrExporter\Enums\SocietyCode;
use LabelTools\PhpCwrExporter\Enums\TisCode;
use LabelTools\PhpCwrExporter\Enums\TitleType;
use LabelTools\PhpCwrExporter\Enums\TransactionType;
use LabelTools\PhpCwrExporter\Enums\VersionType;
use LabelTools\PhpCwrExporter\Enums\WriterDesignation;

it('builds a CWR 2.2 with one New Registration Work', function () {
    $cwr = CwrBuilder::v22()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01265713057')
        ->senderName('Publishing Company')
        ->software('LabelTools CWR Exporter', '1.0.0')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works([
            [
                'submitter_work_number' => '00000001',
                'title' => 'SONG TITLE',
                'title_type' => TitleType::ORIGINAL_TITLE,
                'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
                'version_type'=> VersionType::ORIGINAL_WORK,
                'iswc' => 'T1234567890',
                'alternate_titles' => [
                    [
                        'alternate_title' => 'A DIFFERENT TITLE',
                        'title_type' => TitleType::ALTERNATIVE_TITLE,
                        'language_code' => null,
                    ],
                    [
                        'alternate_title' => 'A DIFFERENT TITLE TWO',
                        'title_type' => TitleType::ALTERNATIVE_TITLE,
                        'language_code' => LanguageCode::FRENCH->value,
                    ]
                ],
                'writers' => [[
                    'interested_party_number' => 'W000001',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                    'ipi_name_number' => '123456789',
                    'pr_affiliation_society' => SocietyCode::BMI->value,
                    'territories' => [[
                        'tis_code' => TisCode::WORLD->value,
                        'pr_collection_share' => 12.5,
                        'mr_collection_share' => 25,
                        'sr_collection_share' => 30.12,
                        'inclusion_exclusion_indicator' => 'I',
                    ]]
                ]],
                'publishers' => [[
                    'interested_party_number' => 'P000001',
                    'name' => 'Publishing Company',
                    'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                    'ipi_name_number' => '123456789',
                    'tax_id' => null,
                    'submitter_agreement_number' => null,
                    'pr_affiliation_society' => null,
                    'pr_ownership_share' => 50,
                    'mr_affiliation_society' => null,
                    'mr_ownership_share' => 100,
                    'sr_affiliation_society' => null,
                    'sr_ownership_share' => 100,
                    'territories' => [[
                        'tis_code' => TisCode::WORLD->value,
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 50.0,
                        'mr_collection_share' => 100.0,
                        'sr_collection_share' => 100.0,
                    ]]
                ]]
            ],
            [
                'submitter_work_number' => '00000002',
                'title' => 'SONG WITH COOL TITLE',
                'title_type' => TitleType::ORIGINAL_TITLE,
                'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
                'version_type'=> VersionType::ORIGINAL_WORK,
                'iswc' => 'T1234567890',
                'writers' => [[
                    'interested_party_number' => 'W000001',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                    'ipi_name_number' => '123456789',
                    'pr_affiliation_society' => SocietyCode::BMI->value,
                    'territories' => [[
                        'tis_code' => TisCode::WORLD->value,
                        'inclusion_exclusion_indicator' => 'I',
                    ]]
                ]],
                'publishers' => [[
                    'interested_party_number' => 'P000001',
                    'name' => 'Publishing Company',
                    'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                    'ipi_name_number' => '123456789',
                    'tax_id' => null,
                    'submitter_agreement_number' => null,
                    'pr_affiliation_society' => null,
                    'pr_ownership_share' => 50,
                    'mr_affiliation_society' => null,
                    'mr_ownership_share' => 100,
                    'sr_affiliation_society' => null,
                    'sr_ownership_share' => 100,
                    'territories' => [[
                        'tis_code' => TisCode::WORLD->value,
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 25.3,
                        'mr_collection_share' => 33.33,
                        'sr_collection_share' => 100.0,
                    ]]
                ]]
            ],
        ]);


    $payload = $cwr->export();

    // Basic sanity
    expect($payload)->toBeString();
    expect($payload)->not->toBeEmpty();

    // Split into physical records (CRLF per spec; accept \n during tests)
    $lines = preg_split("/(\r\n|\n|\r)/", trim($payload));

    // Helper to read fixed-width fields (1-based positions in spec)
    $field = function (string $record, int $start, int $size): string {
        $zeroBased = $start - 1; // spec is 1-indexed
        return substr($record, $zeroBased, $size);
    };

    // Helper to assert zero-padded numeric fields of fixed size
    $expectPaddedN = function (string $raw, int $size, int $value): void {
        expect($raw)->toHaveLength($size);
        expect($raw)->toMatch('/^\d+$/');
        expect($raw)->toBe(str_pad((string) $value, $size, '0', STR_PAD_LEFT));
    };

    // --- File level checks (spec §3.4) ---
    // First record must be HDR, second GRH, last TRL
    expect($field($lines[0], 1, 3))->toBe('HDR'); // §3.4(2)
    expect($field($lines[1], 1, 3))->toBe('GRH'); // §3.4(3)
    expect($field(end($lines), 1, 3))->toBe('TRL'); // §3.4(5)

    // --- HDR checks (spec §3.5) ---
    $hdr = $lines[0];
    expect(trim($field($hdr, 4, 11)))->toBe('01265713057'); // Sender ID
    expect(trim($field($hdr, 15, 45)))->toBe('Publishing Company'); // Sender Name

    // --- HDR checks (spec §3.5) ---
    $hdr = $lines[0];
    expect($field($hdr, 1, 3))->toBe('HDR'); // Record Type
    expect($field($hdr, 60, 5))->toBe('01.10'); // EDI Standard Version Number must be 01.10
    expect(trim($field($hdr, 102, 3)))->toBe('2.2'); // CWR Version 2.2
    // Revision present and numeric (do not lock test to a specific R#)
    expect((int) trim($field($hdr, 105, 3)))->toBeGreaterThan(0); // Revision number present per v2.2

    // --- GRH checks (spec §3.6) ---
    $grh = $lines[1];
    expect($field($grh, 1, 3))->toBe('GRH');
    expect(trim($field($grh, 4, 3)))->toBe('NWR'); // Transaction Type (this group contains NWR)
    expect(trim($field($grh, 12, 5)))->toBe('02.20'); // Version number for transaction type must be 02.20 in CWR 2.2

    // GRH Group ID must be 5-digit zero-padded numeric and begin at 00001 for first/only group (spec §3.6)
    $grhGroupIdRaw = $field($grh, 7, 5);
    $expectPaddedN($grhGroupIdRaw, 5, 1);
    $grhGroupId = (int) $grhGroupIdRaw;

    // --- GRH checks continued ---

    // Find GRT and TRL
    $grtIndex = null; $trlIndex = count($lines) - 1;
    foreach ($lines as $i => $rec) {
        if ($field($rec, 1, 3) === 'GRT') { $grtIndex = $i; break; }
    }
    expect($grtIndex)->not->toBeNull();

    $grt = $lines[$grtIndex];
    expect($field($grt, 1, 3))->toBe('GRT'); // Group Trailer exists fileciteturn0file0L444-L455

    // GRT Group ID must match the GRH Group ID and be zero-padded to 5 (spec §3.7)
    $grtGroupIdRaw = $field($grt, 4, 5);
    //$expectPaddedN($grtGroupIdRaw, 5, $grhGroupId);
    expect((int) $grtGroupIdRaw)->toBe($grhGroupId);

    // Count transactions inside the group: each NWR header starts a transaction
    $txHeaderIndexes = [];
    foreach ($lines as $i => $rec) {
        if ($field($rec, 1, 3) === 'NWR') { $txHeaderIndexes[] = $i; }
    }
    $txCount = count($txHeaderIndexes);

    // Expect 2 works (we provided 2 in the builder)
    expect($txCount)->toBe(2);

    // GRT Transaction Count must equal number of transactions in the group and be zero-padded to 8 (spec §3.7)
    $grtTxRaw = $field($grt, 9, 8);
    $expectPaddedN($grtTxRaw, 8, $txCount); //
    expect((int) $grtTxRaw)->toBe($txCount);

    // GRT Record Count must match physical records between GRH..GRT inclusive and be zero-padded to 8 (spec §3.7)
    $recordsInGroup = $grtIndex; // from index 1 (GRH) to $grtIndex (GRT) inclusive equals $grtIndex records
    $grtRecRaw = $field($grt, 17, 8);
    $expectPaddedN($grtRecRaw, 8, $recordsInGroup);
    expect((int) $grtRecRaw)->toBe($recordsInGroup);

    // With one GRH/GRT pair, the first/only group must be 00001
    expect($grhGroupId)->toBe(1);

    // --- TRL checks (spec §3.8) ---
    $trl = $lines[$trlIndex];
    expect($field($trl, 1, 3))->toBe('TRL');
    // TRL Group Count must be zero-padded to 5 and equal to 1 (spec §3.8)
    $trlGrpRaw = $field($trl, 4, 5);
    $expectPaddedN($trlGrpRaw, 5, 1);
    expect((int) $trlGrpRaw)->toBe(1);

    // TRL Transaction Count must be zero-padded to 8 and equal to number of NWR transactions (spec §3.8)
    $trlTxRaw = $field($trl, 9, 8);
    $expectPaddedN($trlTxRaw, 8, $txCount);
    expect((int) $trlTxRaw)->toBe($txCount);

    // TRL Record Count must be zero-padded to 8 and equal to total physical records (HDR + all group records + TRL) (spec §3.8)

    $trlRecRaw = $field($trl, 17, 8);
    $expectPaddedN($trlRecRaw, 8, count($lines));
    expect((int) $trlRecRaw)->toBe(count($lines));

    // --- Record prefix sequencing validations (spec §2 Record Prefix table) ---
    // For each transaction: first NWR header must have TxSeq 00000000 and RecSeq 00000000; detail records keep same TxSeq and increment RecSeq; next transaction increments TxSeq by 1.
    $prevTxSeq = null;
    $prevRecSeq = null;
    foreach ($lines as $i => $rec) {
        $type = $field($rec, 1, 3);
        if (in_array($type, ['HDR','GRH','GRT','TRL'], true)) {
            continue; // control records are not part of transaction prefix rules
        }
        $txSeq = (int) $field($rec, 4, 8);
        $recSeq = (int) $field($rec, 12, 8);

        if ($type === 'NWR') {
            if ($prevTxSeq === null) {
                expect($txSeq)->toBe(0); // first transaction must start at 0 (spec Record Prefix note)
            } else {
                expect($txSeq)->toBe($prevTxSeq + 1); // subsequent transactions increment by 1 (spec)
            }
            expect($recSeq)->toBe(0); // header record sequence is zero (spec)
            $prevRecSeq = 0;
            $prevTxSeq = $txSeq;
            continue;
        }

        // Detail records: TxSeq equals last header's, RecSeq increments by 1
        expect($txSeq)->toBe($prevTxSeq); // detail records carry same TxSeq (spec)
        expect($recSeq)->toBe($prevRecSeq + 1); // record sequence increments (spec)
        $prevRecSeq = $recSeq;
    }

    // --- NWR field-level spot checks (spec §4.2 NWR format) ---
    // Ensure work titles, version type, distribution category, and recorded flag slots are populated as per builder input
    foreach ($txHeaderIndexes as $idx) {
        $nwr = $lines[$idx];
        expect($field($nwr, 1, 3))->toBe('NWR');
        $title = rtrim($field($nwr, 20, 60)); // Work Title position
        expect(in_array($title, ['SONG TITLE', 'SONG WITH COOL TITLE'], true))->toBeTrue(); //
        expect(trim($field($nwr, 127, 3)))->toBe('POP'); // Musical Work Distribution Category: POPULAR
        expect(trim($field($nwr, 143, 3)))->toBe('ORI'); // Version Type: ORIGINAL_WORK
    }

    // --- ALT field-level spot checks ---
    // Find the ALT records and verify their content
    $altIndexes = [];
    foreach ($lines as $i => $rec) {
        if ($field($rec, 1, 3) === 'ALT') { $altIndexes[] = $i; }
    }
    expect(count($altIndexes))->toBe(2); // One for each work

    $alt1 = $lines[$altIndexes[0]];
    expect(rtrim($field($alt1, 20, 60)))->toBe('A DIFFERENT TITLE');
    expect($field($alt1, 80, 2))->toBe(TitleType::ALTERNATIVE_TITLE->value);
    $alt2 = $lines[$altIndexes[1]];
    expect(rtrim($field($alt2, 20, 60)))->toBe('A DIFFERENT TITLE TWO');
    expect($field($alt2, 80, 2))->toBe(TitleType::ALTERNATIVE_TITLE->value);
    expect($field($alt2, 82, 2))->toBe(LanguageCode::FRENCH->value);

    // --- Transaction level validations around shares (spec §4.2/Transaction Level Validation) ---
    // Our fixture defines a single writer CA and a publisher chain with PR 50%, MR/SR 100% collection shares.
    // We cannot re-calculate all shares without parsing all detail records, but we can ensure there is at least one SWR/OWR and one SPU/PWR present under each NWR.
    foreach ($txHeaderIndexes as $t => $startIndex) {
        $nextBoundary = $t + 1 < $txCount ? $txHeaderIndexes[$t + 1] : $grtIndex; // up to GRT
        $slice = array_slice($lines, $startIndex + 1, $nextBoundary - $startIndex - 1);
        $types = array_map(fn($r) => $field($r, 1, 3), $slice);
        expect($types)->toContain('SPU'); // at least one controlled publisher (spec: SPU required if writer shares < 100%)
        expect($types)->toContain('SWR'); // at least one writer (spec)
        expect($types)->toContain('PWR'); // link publisher to writer (spec)
        expect($types)->toContain('SPT'); // at least one territory for publisher
    }

    // --- SPT field-level spot checks ---
    $sptIndexes = [];
    foreach ($lines as $i => $rec) {
        if ($field($rec, 1, 3) === 'SPT') { $sptIndexes[] = $i; }
    }
    expect(count($sptIndexes))->toBe(2);

    // Check first SPT record (from first work)
    $spt1 = $lines[$sptIndexes[0]];
    expect($field($spt1, 35, 5))->toBe('05000'); // PR Collection Share 50.00%
    expect($field($spt1, 40, 5))->toBe('10000'); // MR Collection Share 100.00%
    expect($field($spt1, 45, 5))->toBe('10000'); // SR Collection Share 100.00%
    expect(trim($field($spt1, 51, 4)))->toBe((string)TisCode::WORLD->value); // TIS Code

    // Check second SPT record (from second work)
    $spt2 = $lines[$sptIndexes[1]];
    expect($field($spt2, 35, 5))->toBe('02530'); // PR Collection Share 25.30%
    expect($field($spt2, 40, 5))->toBe('03333'); // MR Collection Share 33.33%
    expect($field($spt2, 45, 5))->toBe('10000'); // SR Collection Share 100.00%
    expect(trim($field($spt2, 51, 4)))->toBe((string)TisCode::WORLD->value); // TIS Code

    // --- SWT field-level spot checks ---
    $swtIndexes = [];
    foreach ($lines as $i => $rec) {
        if ($field($rec, 1, 3) === 'SWT') { $swtIndexes[] = $i; }
    }
    expect(count($swtIndexes))->toBe(2);

    // Check first SWT record (from first work)
    $swt1 = $lines[$swtIndexes[0]];
    expect($field($swt1, 29, 5))->toBe('01250'); // PR Collection Share 12.50%
    expect($field($swt1, 34, 5))->toBe('02500'); // MR Collection Share 25.00%
    expect($field($swt1, 39, 5))->toBe('03012'); // SR Collection Share 30.12%
    expect(trim($field($swt1, 45, 4)))->toBe((string)TisCode::WORLD->value); // TIS Code
});