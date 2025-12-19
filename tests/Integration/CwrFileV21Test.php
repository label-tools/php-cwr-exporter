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

if (!function_exists('assertCwrRule18DetailOrderV21')) {
    /**
     * @param string[] $lines
     */
    function assertCwrRule18DetailOrderV21(array $lines): void
    {
        $orderGroups = [
            ['NWR', 'REV', 'ISW', 'EXC'],
            ['SPU'],
            ['NPN'],
            ['SPT'],
            ['OPU'],
            ['NPN'],
            ['SWR'],
            ['NWN'],
            ['SWT'],
            ['PWR'],
            ['OWR'],
            ['NWN'],
            ['ALT'],
            ['NAT'],
            ['EWT'],
            ['NET'],
            ['NOW'],
            ['VER'],
            ['NVT'],
            ['NOW'],
            ['PER'],
            ['NPR'],
            ['REC'],
            ['ORN'],
            ['INS'],
            ['IND'],
            ['COM'],
            ['NCT'],
            ['NOW'],
            ['ARI'],
        ];

        static $recordStages = null;
        if ($recordStages === null) {
            $recordStages = [];
            foreach ($orderGroups as $stage => $codes) {
                $stageNumber = $stage + 1;
                foreach ($codes as $code) {
                    $recordStages[$code][] = $stageNumber;
                }
            }
        }

        $startTypes = ['NWR', 'REV', 'ISW', 'EXC'];
        $currentStage = 0;
        $checked = false;

        foreach ($lines as $line) {
            $type = substr($line, 0, 3);
            if ($type === '' || in_array($type, ['HDR', 'GRH', 'GRT', 'TRL'], true)) {
                continue;
            }

            if (in_array($type, $startTypes, true)) {
                $currentStage = 0;
            }

            $stages = $recordStages[$type] ?? null;
            if ($stages === null) {
                continue;
            }

            $matchedStage = null;
            foreach ($stages as $stage) {
                if ($stage >= $currentStage) {
                    $matchedStage = $stage;
                    break;
                }
            }

            expect($matchedStage)->not->toBeNull();
            $currentStage = $matchedStage;
            $checked = true;
        }

        expect($checked)->toBeTrue();
    }
}


it('real test 2.1', function () {
    $cwr = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01265713057')
        ->senderName('Publishing Company')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works([
    [
        'submitter_work_number' => '0000000071',
        'title' => 'OKOA',
        'title_type' => TitleType::ORIGINAL_TITLE,
        'language' => null,
        'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
        'version_type' => VersionType::ORIGINAL_WORK,
        'iswc' => 'T3307522670',
        'duration' => null,
        'copyright_date' => null,
        'copyright_number' => null,
        'recorded' => true,
        'text_music_relationship' => '',
        'performing_artists' => [
            [
                'last_name' => 'Ben & Vincent',
                'first_name' => null,
                'ipi_name_number' => null,
                'ipi_base_number' => null,
            ],
            [
                'last_name' => 'Xique-Xique',
                'first_name' => null,
                'ipi_name_number' => null,
                'ipi_base_number' => null,
            ],
        ],
        'alternate_titles' => [],
        'writers' => [
            [
                'interested_party_number' => 'W000008',
                'first_name' => 'BENDAD',
                'last_name' => 'THOMAS',
                'designation_code' => 'C',
                'ipi_name_number' => '01278333828',
                'pr_affiliation_society' => 10,
                'pr_ownership_share' => 0,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
                'territories' => [
                    [
                        'tis_code' => 2136,
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 50.0,
                        'mr_collection_share' => 0,
                        'sr_collection_share' => 0,
                    ],
                ],
                'controlled' => true,
                'publisher_interested_party_number' => 'P000001',
            ],
        ],
        'publishers' => [
            [
                'interested_party_number' => 'P000001',
                'name' => 'WAYU PUBLISHING (ASCAP)',
                'type' => 'E',
                'ipi_name_number' => '01265713057',
                'pr_affiliation_society' => 10,
                'pr_ownership_share' => 50.0,
                'mr_affiliation_society' => null,
                'mr_ownership_share' => 100.0,
                'sr_affiliation_society' => null,
                'sr_ownership_share' => 100.0,
                'territories' => [
                    [
                        'tis_code' => 2136,
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 50.0,
                        'mr_collection_share' => 100.0,
                        'sr_collection_share' => 100.0,
                    ],
                ],
            ],
        ],
        'recordings' => [
            [
                'first_release_date' => '20230122',
                'first_release_duration' => '000352',
                'first_album_title' => 'Okoa',
                'first_album_label' => null,
                'first_release_catalog_number' => null,
                'first_release_ean' => null,
                'first_release_isrc' => 'US83Z2225646',
                'recording_format' => 'A',
                'recording_technique' => 'D',
                'media_type' => null,
            ],
            [
                'first_release_date' => '20220814',
                'first_release_duration' => '000518',
                'first_album_title' => 'Okoa (Xique-Xique Remix) - Single',
                'first_album_label' => null,
                'first_release_catalog_number' => null,
                'first_release_ean' => null,
                'first_release_isrc' => 'US83Z2225648',
                'recording_format' => 'A',
                'recording_technique' => 'D',
                'media_type' => null,
            ],
        ],
    ],
]);


    $payload = $cwr->export();

    $skipped = $cwr->getSkippedWorks();

    expect($skipped)->toHaveCount(1);
    expect($skipped[0]['work_number'])->toBe('0000000071');
    expect($skipped[0]['error'])->toContain('100');

    expect($payload)->toBeString();
    expect($payload)->not->toBeEmpty();
    expect($payload)->not->toContain('OKOA');
});

it('renders detail records in correct order defined by spec for v21', function () {
    $cwr = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01234567890')
        ->senderName('Order Validator')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works([
            [
                'submitter_work_number' => 'ORDERTEST1',
                'title' => 'Rule 18 Order',
                'title_type' => TitleType::ORIGINAL_TITLE,
                'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
                'version_type'=> VersionType::ORIGINAL_WORK,
                'writers' => [[
                    'controlled' => true,
                    'interested_party_number' => 'W000001',
                    'last_name' => 'Writer',
                    'first_name' => 'Order',
                    'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                    'publisher_interested_party_number' => 'P000001',
                    'pr_ownership_share' => 50,
                    'mr_ownership_share' => 0,
                    'sr_ownership_share' => 0,
                    'territories' => [[
                        'tis_code' => TisCode::WORLD->value,
                        'inclusion_exclusion_indicator' => 'I',
                    ]]
                ]],
                'publishers' => [[
                    'interested_party_number' => 'P000001',
                    'name' => 'Order Pub',
                    'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                    'ipi_name_number' => '123456789',
                    'pr_ownership_share' => 50,
                    'mr_ownership_share' => 100,
                    'sr_ownership_share' => 100,
                    'territories' => [[
                        'tis_code' => TisCode::WORLD->value,
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 50,
                        'mr_collection_share' => 100,
                        'sr_collection_share' => 100,
                    ]]
                ]],
                'alternate_titles' => [[
                    'alternate_title' => 'Alternate Order',
                    'title_type' => TitleType::ALTERNATIVE_TITLE,
                ]],
                'performing_artists' => [[
                    'last_name' => 'Performer',
                ]],
                'recordings' => [[
                    'first_release_date' => '20240101',
                    'first_release_duration' => '000200',
                ]],
            ],
        ]);

    $payload = $cwr->export();
    $lines = preg_split("/(\r\n|\n|\r)/", trim($payload));
    assertCwrRule18DetailOrderV21($lines);
});

it('builds a CWR 2.1 with some new works using works array', function () {
    $cwr = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01265713057')
        ->senderName('Publishing Company')
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
                    'controlled' => true,
                    'interested_party_number' => 'W000001',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                    'ipi_name_number' => '123456789',
                    'pr_affiliation_society' => SocietyCode::BMI->value,
                    'publisher_interested_party_number' => 'P000001', //use for linking to publisher
                    'pr_ownership_share' => 50,
                    'mr_affiliation_society' => null,
                    'mr_ownership_share' => 0,
                    'sr_affiliation_society' => null,
                    'sr_ownership_share' => 0,
                    'territories' => [[
                        'tis_code' => TisCode::WORLD->value,
                        'pr_collection_share' => 50,
                        'mr_collection_share' => 0,
                        'sr_collection_share' => 0,
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
                        'pr_collection_share' => 50,
                        'mr_collection_share' => 100,
                        'sr_collection_share' => 100,
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
                    'controlled' => true,
                    'interested_party_number' => 'W000001',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                    'ipi_name_number' => '123456789',
                    'pr_affiliation_society' => SocietyCode::BMI->value,
                    'publisher_interested_party_number' => 'P000001',
                    'pr_ownership_share' => 50,
                    'mr_affiliation_society' => null,
                    'mr_ownership_share' => 0,
                    'sr_affiliation_society' => null,
                    'sr_ownership_share' => 0,
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
                        'pr_collection_share' => 50,
                        'mr_collection_share' => 100,
                        'sr_collection_share' => 100,
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
    expect(trim($field($hdr, 15, 45)))->toBe('PUBLISHING COMPANY'); // Sender Name

    // --- HDR checks (spec §3.5) ---
    $hdr = $lines[0];
    expect($field($hdr, 1, 3))->toBe('HDR'); // Record Type
    expect($field($hdr, 60, 5))->toBe('01.10'); // EDI Standard Version Number must be 01.10
    // CWR v2.1 HDR record is 101 characters long. It should not contain v2.2 fields.
    expect(strlen($hdr))->toBe(101);
    // The following fields from CWR v2.2 should not exist.
    expect(trim($field($hdr, 102, 66)))->toBeEmpty();

    // --- GRH checks (spec §3.6) ---
    $grh = $lines[1];
    expect($field($grh, 1, 3))->toBe('GRH');
    expect(trim($field($grh, 4, 3)))->toBe('NWR'); // Transaction Type (this group contains NWR)
    expect(trim($field($grh, 12, 5)))->toBe('02.10'); // Version number for transaction type must be 02.10 in CWR 2.1

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
    expect($field($grt, 1, 3))->toBe('GRT');

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

    // --- Transaction-level validations and PWR linking checks ---
    // Get records for the first transaction (Work 1)
    $tx1_slice = array_slice($lines, $txHeaderIndexes[0] + 1, $txHeaderIndexes[1] - $txHeaderIndexes[0] - 1);
    $tx1_types = array_map(fn($r) => $field($r, 1, 3), $tx1_slice);

    // Assert basic record presence for Work 1
    expect($tx1_types)->toContain('SPU'); // Publisher
    expect($tx1_types)->toContain('SWR'); // Writer
    expect($tx1_types)->toContain('SPT'); // Publisher Territory

    // Assert PWR record EXISTS for Work 1, as the writer has a publisher_interested_party_number
    expect($tx1_types)->toContain('PWR');

    // Find the PWR record and check its structure for CWR v2.1 compliance
    $pwrRecord = null;
    foreach ($tx1_slice as $record) {
        if ($field($record, 1, 3) === 'PWR') {
            $pwrRecord = $record;
            break;
        }
    }
    expect($pwrRecord)->not->toBeNull();
    // CWR v2.1 PWR record is 110 characters (includes Writer IP Number) and does not have the Publisher Sequence # from v2.2.
    expect(strlen($pwrRecord))->toBe(110);

    // Get records for the second transaction (Work 2)
    $tx2_slice = array_slice($lines, $txHeaderIndexes[1] + 1, $grtIndex - $txHeaderIndexes[1] - 1);
    $tx2_types = array_map(fn($r) => $field($r, 1, 3), $tx2_slice);

    // Assert PWR record EXISTS for Work 2, as the writer is linked to a publisher
    expect($tx2_types)->toContain('PWR');

    // Ensure every SWR in the file is followed by at least one PWR before the next SWR/NWR
    foreach ($lines as $idx => $rec) {
        if ($field($rec, 1, 3) !== 'SWR') {
            continue;
        }

        $foundPwr = false;
        for ($cursor = $idx + 1; $cursor < count($lines); $cursor++) {
            $type = $field($lines[$cursor], 1, 3);
            if ($type === 'PWR') {
                $foundPwr = true;
                break;
            }
            if (in_array($type, ['SWR', 'NWR'], true)) {
                break;
            }
        }

        expect($foundPwr)->toBeTrue();
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
    expect($field($spt1, 40, 5))->toBe('10000'); // MR Collection Share 75.00%
    expect($field($spt1, 45, 5))->toBe('10000'); // SR Collection Share 69.88%
    expect(trim($field($spt1, 51, 4)))->toBe((string)TisCode::WORLD->value); // TIS Code

    // Check second SPT record (from second work)
    $spt2 = $lines[$sptIndexes[1]];
    expect($field($spt2, 35, 5))->toBe('05000');
    expect($field($spt2, 40, 5))->toBe('10000');
    expect($field($spt2, 45, 5))->toBe('10000');
    expect(trim($field($spt2, 51, 4)))->toBe((string)TisCode::WORLD->value); // TIS Code

    // --- SWT field-level spot checks ---
    $swtIndexes = [];
    foreach ($lines as $i => $rec) {
        if ($field($rec, 1, 3) === 'SWT') { $swtIndexes[] = $i; }
    }
    expect(count($swtIndexes))->toBe(2);

    // Check first SWT record (from first work)
    $swt1 = $lines[$swtIndexes[0]];
    expect($field($swt1, 29, 5))->toBe('05000'); // PR Collection Share 12.50%
    expect($field($swt1, 34, 5))->toBe('00000'); // MR Collection Share 25.00%
    expect($field($swt1, 39, 5))->toBe('00000'); // SR Collection Share 30.12%
    expect(trim($field($swt1, 45, 4)))->toBe((string)TisCode::WORLD->value); // TIS Code
});

it('keeps transaction prefixes contiguous when a work is skipped', function () {
    $works = [
        [
            'submitter_work_number' => '00000001',
            'title' => 'VALID ONE',
            'title_type' => TitleType::ORIGINAL_TITLE,
            'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
            'version_type'=> VersionType::ORIGINAL_WORK,
            'writers' => [[
                'interested_party_number' => 'W000001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'ipi_name_number' => '123456789',
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'publisher_interested_party_number' => 'P000001',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
            ]],
            'publishers' => [[
                'interested_party_number' => 'P000001',
                'name' => 'Publishing Company',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 100,
                'sr_ownership_share' => 100,
            ]]
        ],
        [
            // This work will be skipped because the ISWC is invalid, triggering a validation error.
            'submitter_work_number' => '00000002',
            'title' => 'SHOULD SKIP',
            'title_type' => TitleType::ORIGINAL_TITLE,
            'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
            'version_type'=> VersionType::ORIGINAL_WORK,
            'iswc' => 'BADISWC',
            'writers' => [[
                'interested_party_number' => 'W000002',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'ipi_name_number' => '123456789',
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'publisher_interested_party_number' => 'P000002',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
            ]],
            'publishers' => [[
                'interested_party_number' => 'P000002',
                'name' => 'Publishing Company',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 100,
                'sr_ownership_share' => 100,
            ]]
        ],
        [
            'submitter_work_number' => '00000003',
            'title' => 'VALID TWO',
            'title_type' => TitleType::ORIGINAL_TITLE,
            'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
            'version_type'=> VersionType::ORIGINAL_WORK,
            'writers' => [[
                'interested_party_number' => 'W000003',
                'first_name' => 'Jake',
                'last_name' => 'Doe',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'ipi_name_number' => '123456789',
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'publisher_interested_party_number' => 'P000003',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
            ]],
            'publishers' => [[
                'interested_party_number' => 'P000003',
                'name' => 'Publishing Company',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 100,
                'sr_ownership_share' => 100,
            ]]
        ],
    ];

    $cwr = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01265713057')
        ->senderName('Publishing Company')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works($works);

    $payload = $cwr->export();
    $lines = preg_split("/(\r\n|\n|\r)/", trim($payload));

    $field = function (string $record, int $start, int $size): string {
        $zeroBased = $start - 1;
        return substr($record, $zeroBased, $size);
    };

    $detailLines = array_values(array_filter(
        $lines,
        fn($rec) => !in_array($field($rec, 1, 3), ['HDR', 'GRH', 'GRT', 'TRL'], true)
    ));

    // Only two NWR headers should remain; the bad work is skipped entirely.
    $nwrTxSeqs = [];
    $prevTxSeq = null;
    $prevRecSeq = null;
    foreach ($detailLines as $rec) {
        $type = $field($rec, 1, 3);
        $txSeq = (int) $field($rec, 4, 8);
        $recSeq = (int) $field($rec, 12, 8);

        if ($type === 'NWR') {
            $nwrTxSeqs[] = $txSeq;
            if ($prevTxSeq === null) {
                expect($txSeq)->toBe(0);
            } else {
                expect($txSeq)->toBe($prevTxSeq + 1);
            }
            expect($recSeq)->toBe(0);
            $prevTxSeq = $txSeq;
            $prevRecSeq = 0;
            continue;
        }

        // Detail records must keep the same TxSeq and increment RecSeq by 1.
        expect($txSeq)->toBe($prevTxSeq);
        expect($recSeq)->toBe($prevRecSeq + 1);
        $prevRecSeq = $recSeq;
    }

    expect($nwrTxSeqs)->toBe([0, 1]);
});

it('skips works where a controlled writer cannot produce a PWR record', function () {
    $works = [
        [
            'submitter_work_number' => 'GOODPWR1',
            'title' => 'VALID WITH PUBLISHER LINK',
            'title_type' => TitleType::ORIGINAL_TITLE,
            'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
            'version_type'=> VersionType::ORIGINAL_WORK,
            'writers' => [[
                'interested_party_number' => 'WVAL001',
                'first_name' => 'Linked',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'ipi_name_number' => '123456789',
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'publisher_interested_party_number' => 'PVAL001',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
            ]],
            'publishers' => [[
                'interested_party_number' => 'PVAL001',
                'name' => 'Valid Publishing',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 100,
                'sr_ownership_share' => 100,
            ]]
        ],
        [
            'submitter_work_number' => 'BADPWR',
            'title' => 'MISSING PUBLISHER LINK',
            'title_type' => TitleType::ORIGINAL_TITLE,
            'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
            'version_type'=> VersionType::ORIGINAL_WORK,
            'writers' => [[
                'interested_party_number' => 'WORPHAN',
                'first_name' => 'Orphaned',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'ipi_name_number' => '123456789',
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
                // intentionally missing publisher_interested_party_number to trigger validation
            ]],
            'publishers' => [[
                'interested_party_number' => 'P000BAD',
                'name' => 'Missing Link Pub',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 100,
                'sr_ownership_share' => 100,
            ]]
        ],
    ];

    $cwr = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01265713057')
        ->senderName('Publishing Company')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works($works);

    $payload = $cwr->export();
    $lines = preg_split("/(\r\n|\n|\r)/", trim($payload));

    $field = function (string $record, int $start, int $size): string {
        $zeroBased = $start - 1;
        return substr($record, $zeroBased, $size);
    };

    $detailLines = array_values(array_filter(
        $lines,
        fn($rec) => !in_array($field($rec, 1, 3), ['HDR', 'GRH', 'GRT', 'TRL'], true)
    ));
    $detailTypes = array_map(fn($rec) => $field($rec, 1, 3), $detailLines);

    $nwrCount = count(array_filter($detailTypes, fn($t) => $t === 'NWR'));
    expect($nwrCount)->toBe(1); // the BADPWR work is skipped entirely
    expect($payload)->not->toContain('BADPWR');

    $swrIndexes = [];
    foreach ($detailLines as $idx => $rec) {
        if ($field($rec, 1, 3) === 'SWR') {
            $swrIndexes[] = $idx;
        }
    }
    expect($swrIndexes)->toHaveCount(1);

    $swrIdx = $swrIndexes[0];
    $foundPwr = false;
    for ($i = $swrIdx + 1; $i < count($detailLines); $i++) {
        $type = $field($detailLines[$i], 1, 3);
        if ($type === 'PWR') {
            $foundPwr = true;
            break;
        }
        if ($type === 'NWR') {
            break;
        }
    }
    expect($foundPwr)->toBeTrue();
});

it('reports skipped works with errors', function () {
    $works = [
        [
            'submitter_work_number' => 'VALID1',
            'title' => 'VALID',
            'title_type' => TitleType::ORIGINAL_TITLE,
            'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
            'version_type'=> VersionType::ORIGINAL_WORK,
            'writers' => [[
                'interested_party_number' => 'W000001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'pr_ownership_share' => 50,
                'publisher_interested_party_number' => 'P000001',
            ]],
            'publishers' => [[
                'interested_party_number' => 'P000001',
                'name' => 'Publishing Company',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 100,
                'sr_ownership_share' => 100,
                'territories' => [[
                    'tis_code' => TisCode::WORLD->value,
                    'pr_collection_share' => 50.0,
                ]],
            ]],
        ],
        [
            'submitter_work_number' => 'BAD1',
            'title' => 'MISSING PUBLISHER LINK',
            'title_type' => TitleType::ORIGINAL_TITLE,
            'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
            'version_type'=> VersionType::ORIGINAL_WORK,
            'writers' => [[
                'interested_party_number' => 'WORPHAN',
                'first_name' => 'Orphaned',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'pr_ownership_share' => 50,
                // missing publisher_interested_party_number triggers skip
            ]],
            'publishers' => [[
                'interested_party_number' => 'P000BAD',
                'name' => 'Missing Link Pub',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50, // satisfy ownership check so we reach PWR validation
            ]],
        ],
    ];

    $cwr = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01265713057')
        ->senderName('Publishing Company')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works($works);

    $payload = $cwr->export();
    expect($payload)->not->toBeEmpty();

    $skipped = $cwr->getSkippedWorks();
    expect($skipped)->toHaveCount(1);
    expect($skipped[0]['work_number'])->toBe('BAD1');
    expect($skipped[0]['error'])->toContain('publisher_interested_party_number');
});

it('does not emit partially rendered works when a later record fails validation', function () {
    $works = [
        [
            'submitter_work_number' => 'GOOD1',
            'title' => 'GOOD ONE',
            'title_type' => TitleType::ORIGINAL_TITLE,
            'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
            'version_type'=> VersionType::ORIGINAL_WORK,
            'writers' => [[
                'interested_party_number' => 'WG001',
                'first_name' => 'John',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'pr_ownership_share' => 50,
                'publisher_interested_party_number' => 'PG001',
            ]],
            'publishers' => [[
                'interested_party_number' => 'PG001',
                'name' => 'Good Pub 1',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50,
                'territories' => [[
                    'tis_code' => TisCode::WORLD->value,
                    'pr_collection_share' => 50,
                    'inclusion_exclusion_indicator' => 'I',
                ]],
            ]],
        ],
        [
            'submitter_work_number' => 'BADMID',
            'title' => 'FAILS MID RENDER',
            'title_type' => TitleType::ORIGINAL_TITLE,
            'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
            'version_type'=> VersionType::ORIGINAL_WORK,
            'writers' => [[
                'interested_party_number' => 'WBMID',
                'first_name' => 'Bad',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'pr_ownership_share' => 50,
                'publisher_interested_party_number' => 'PBMID',
            ]],
            'publishers' => [[
                'interested_party_number' => 'PBMID',
                'name' => 'Bad Pub',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50,
                'territories' => [
                    [
                        'tis_code' => TisCode::WORLD->value,
                        'pr_collection_share' => 25,
                        'inclusion_exclusion_indicator' => 'I',
                    ],
                    [
                        'tis_code' => TisCode::WORLD->value,
                        'pr_collection_share' => 25,
                        'inclusion_exclusion_indicator' => 'X', // invalid, triggers SPT validation
                    ],
                ],
            ]],
        ],
        [
            'submitter_work_number' => 'GOOD2',
            'title' => 'GOOD TWO',
            'title_type' => TitleType::ORIGINAL_TITLE,
            'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
            'version_type'=> VersionType::ORIGINAL_WORK,
            'writers' => [[
                'interested_party_number' => 'WG002',
                'first_name' => 'Jane',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'pr_ownership_share' => 50,
                'publisher_interested_party_number' => 'PG002',
            ]],
            'publishers' => [[
                'interested_party_number' => 'PG002',
                'name' => 'Good Pub 2',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50,
                'territories' => [[
                    'tis_code' => TisCode::WORLD->value,
                    'pr_collection_share' => 50,
                    'inclusion_exclusion_indicator' => 'I',
                ]],
            ]],
        ],
    ];

    $cwr = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01265713057')
        ->senderName('Publishing Company')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works($works);

    $payload = $cwr->export();
    $lines = preg_split("/(\r\n|\n|\r)/", trim($payload));

    $field = function (string $record, int $start, int $size): string {
        $zeroBased = $start - 1;
        return substr($record, $zeroBased, $size);
    };

    $detailLines = array_values(array_filter(
        $lines,
        fn($rec) => !in_array($field($rec, 1, 3), ['HDR', 'GRH', 'GRT', 'TRL'], true)
    ));
    $detailTypes = array_map(fn($rec) => $field($rec, 1, 3), $detailLines);

    $nwrSeqs = [];
    foreach ($detailLines as $rec) {
        if ($field($rec, 1, 3) === 'NWR') {
            $nwrSeqs[] = (int) $field($rec, 4, 8);
        }
    }

    expect($detailTypes)->not->toContain('BADMID');
    expect($payload)->not->toContain('BADMID');
    expect($nwrSeqs)->toBe([0, 1]);

    $trl = end($lines);
    expect($field($trl, 1, 3))->toBe('TRL');
    expect((int) $field($trl, 9, 8))->toBe(2);

    $skipped = $cwr->getSkippedWorks();
    expect($skipped)->toHaveCount(1);
    expect($skipped[0]['work_number'])->toBe('BADMID');
    expect($skipped[0]['error'])->toContain('Inclusion/Exclusion Indicator');
});

it('builds a CWR 2.1 with some new works using addWork', function () {
    $works = [[
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
            'publisher_interested_party_number' => 'P000001', //use for linking to publisher
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
                'mr_collection_share' => 75.0,
                'sr_collection_share' => 69.88,
            ]]
        ]]
    ], [
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
            'publisher_interested_party_number' => 'P000001',
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
    ]];

    $cwr = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01265713057')
        ->senderName('Publishing Company')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value);

    foreach ($works as $work) {
        $cwr->addWork($work);
    }

    $payload = $cwr->export();

    // Basic sanity
    expect($payload)->toBeString();
    expect($payload)->not->toBeEmpty();
    expect(count($cwr->getWorks()))->toBe(2);
});

it('builds a CWR 2.1 file and writes it to a stream', function () {
    $works = [
        [
            'submitter_work_number' => '00000001',
            'title' => 'SONG TITLE',
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
            'publisher_interested_party_number' => 'P000001',
        ]],
        'publishers' => [[
            'interested_party_number' => 'P000001',
            'name' => 'Publishing Company',
            'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 100,
                'sr_ownership_share' => 100,
            ]]
        ],
    ];

    $cwr = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01265713057')
        ->senderName('Publishing Company')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works($works);

    $stream = fopen('php://memory', 'r+');
    $cwr->exportToStream($stream);

    rewind($stream);
    $payload = stream_get_contents($stream);
    fclose($stream);

    // Basic sanity
    expect($payload)->toBeString();
    expect($payload)->not->toBeEmpty();
});

it('emits an OPU record for uncontrolled publishers', function () {
    $works = [[
        'submitter_work_number' => 'WORK00099',
        'title' => 'UNCONTROLLED PUBLISHER SONG',
        'title_type' => TitleType::ORIGINAL_TITLE,
        'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
        'version_type'=> VersionType::ORIGINAL_WORK,
            'writers' => [[
                'interested_party_number' => 'WOPU001',
                'last_name' => 'Writer',
                'first_name' => 'Uncontrolled',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'pr_ownership_share' => 70,
                'publisher_interested_party_number' => 'P000001',
                'territories' => [[
                    'tis_code' => TisCode::WORLD->value,
                    'pr_collection_share' => 25,
                    'inclusion_exclusion_indicator' => 'I',
                ]]
        ]],
        'publishers' => [[
            'interested_party_number' => 'P000001',
            'name' => 'Other Publishing Co',
            'type' => PublisherType::ORIGINAL_PUBLISHER->value,
            'ipi_name_number' => '123456789',
            'pr_ownership_share' => 30,
            'controlled' => false,
        ]]
    ]];

    $payload = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('00000000000')
        ->senderName('Testing Co')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works($works)
        ->export();

    $lines = preg_split("/(\r\n|\n|\r)/", trim($payload));
    $field = function (string $record, int $start, int $size): string {
        $zeroBased = $start - 1;
        return substr($record, $zeroBased, $size);
    };

    $detailRecords = array_filter($lines, fn($rec) => !in_array($field($rec, 1, 3), ['HDR', 'GRH', 'GRT', 'TRL'], true));
    $recordTypes = array_map(fn($rec) => $field($rec, 1, 3), $detailRecords);

    expect($recordTypes)->toContain('OPU');
    expect($recordTypes)->not->toContain('SPU');
});

it('emits an OPU with unknown publisher indicator while keeping PWR linked to the controlled publisher', function () {
    $works = [[
        'submitter_work_number' => 'UNKOPU01',
        'title' => 'UNKNOWN PUBLISHER MIX',
        'title_type' => TitleType::ORIGINAL_TITLE,
        'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
        'version_type'=> VersionType::ORIGINAL_WORK,
        'writers' => [
            [
                'interested_party_number' => 'WCTRL001',
                'first_name' => 'Controlled',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'publisher_interested_party_number' => 'PCON001',
                'pr_ownership_share' => 25,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
                'territories' => [[
                    'tis_code' => TisCode::WORLD->value,
                    'pr_collection_share' => 25,
                    'mr_collection_share' => 0,
                    'sr_collection_share' => 0,
                    'inclusion_exclusion_indicator' => 'I',
                ]],
            ],
            [
                'controlled' => false,
                'interested_party_number' => 'WUNCTRL1',
                'first_name' => 'Uncontrolled',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'pr_affiliation_society' => SocietyCode::BMI->value,
                'pr_ownership_share' => 25,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
                'territories' => [[
                    'tis_code' => TisCode::WORLD->value,
                    'pr_collection_share' => 25,
                    'mr_collection_share' => 0,
                    'sr_collection_share' => 0,
                    'inclusion_exclusion_indicator' => 'I',
                ]],
            ],
        ],
        'publishers' => [
            [
                'interested_party_number' => 'PCON001',
                'name' => 'CONTROLLED PUBLISHING',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 25,
                'mr_ownership_share' => 50,
                'sr_ownership_share' => 50,
                'territories' => [[
                    'tis_code' => TisCode::WORLD->value,
                    'pr_collection_share' => 25,
                    'mr_collection_share' => 50,
                    'sr_collection_share' => 50,
                    'inclusion_exclusion_indicator' => 'I',
                ]],
            ],
            [
                'controlled' => false,
                'publisher_unknown_indicator' => true,
                'pr_ownership_share' => 25,
                'mr_ownership_share' => 50,
                'sr_ownership_share' => 50,
                'territories' => [[
                    'tis_code' => TisCode::WORLD->value,
                    'pr_collection_share' => 25,
                    'mr_collection_share' => 50,
                    'sr_collection_share' => 50,
                    'inclusion_exclusion_indicator' => 'I',
                ]],
            ],
        ],
    ]];

    $payload = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('00000000000')
        ->senderName('Testing Co')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works($works)
        ->export();

    $lines = preg_split("/(\\r\\n|\\n|\\r)/", trim($payload));
    $field = function (string $record, int $start, int $size): string {
        $zeroBased = $start - 1;
        return substr($record, $zeroBased, $size);
    };

    $detailRecords = array_values(array_filter($lines, fn($rec) => !in_array($field($rec, 1, 3), ['HDR', 'GRH', 'GRT', 'TRL'], true)));
    $recordTypes = array_map(fn($rec) => $field($rec, 1, 3), $detailRecords);

    expect($recordTypes)->toContain('SPU');
    expect($recordTypes)->toContain('OPU');
    expect($recordTypes)->toContain('SWR');
    expect($recordTypes)->toContain('OWR');
    expect($recordTypes)->toContain('PWR');

    $opu = array_values(array_filter($detailRecords, fn($rec) => $field($rec, 1, 3) === 'OPU'))[0];
    expect(trim($field($opu, 31, 45)))->toBe('');
    expect($field($opu, 76, 1))->toBe('Y');
    expect($field($opu, 116, 5))->toBe('02500');
    expect($field($opu, 124, 5))->toBe('05000');
    expect($field($opu, 132, 5))->toBe('05000');

    $pwr = array_values(array_filter($detailRecords, fn($rec) => $field($rec, 1, 3) === 'PWR'))[0];
    expect(trim($field($pwr, 20, 9)))->toBe('PCON001');
    expect(trim($field($pwr, 29, 45)))->toBe('CONTROLLED PUBLISHING');
});

it('emits a REC record when recording detail is provided', function () {
    $works = [[
        'submitter_work_number' => 'WORKREC01',
        'title' => 'REC SONG',
        'title_type' => TitleType::ORIGINAL_TITLE,
        'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
        'version_type'=> VersionType::ORIGINAL_WORK,
        'recordings' => [[
            'first_release_date' => '20240101',
            'first_release_duration' => '000400',
            'first_album_title' => 'ALBUM ONE',
            'first_album_label' => 'LABEL INC',
            'first_release_catalog_number' => 'CAT001',
            'first_release_ean' => '0001234567890',
            'first_release_isrc' => 'USRC17607839',
            'recording_format' => 'A',
            'recording_technique' => 'D',
            'media_type' => 'AUD',
        ]],
        'performing_artists' => [[
            'last_name' => 'Singer',
            'first_name' => 'Lead',
            'ipi_name_number' => '12345678901',
            'ipi_base_number' => 'PG12345678901',
        ]],
        'writers' => [[
                'interested_party_number' => 'WREC001',
                'last_name' => 'Singer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 50,
                'sr_ownership_share' => 50,
                'publisher_interested_party_number' => 'P000001',
                'territories' => [[
                    'tis_code' => TisCode::WORLD->value,
                    'inclusion_exclusion_indicator' => 'I',
                ]]
        ]],
        'publishers' => [[
            'interested_party_number' => 'P000001',
            'name' => 'Publishing Company',
            'type' => PublisherType::ORIGINAL_PUBLISHER->value,
            'pr_ownership_share' => 50,
            'mr_ownership_share' => 50,
            'sr_ownership_share' => 50,
            'ipi_name_number' => '123456789',
        ]]
    ]];

    $payload = CwrBuilder::v21()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('00000000000')
        ->senderName('Recording Co')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works($works)
        ->export();

    $lines = preg_split("/(\r\n|\n|\r)/", trim($payload));
    $field = function (string $record, int $start, int $size): string {
        $zeroBased = $start - 1;
        return substr($record, $zeroBased, $size);
    };

    $recLines = array_filter($lines, fn($rec) => $field($rec, 1, 3) === 'REC');
    expect($recLines)->not->toBeEmpty();
    $firstRec = array_values($recLines)[0];
    expect($field($firstRec, 20, 8))->toBe('20240101');
    $perLines = array_filter($lines, fn($rec) => $field($rec, 1, 3) === 'PER');
    expect($perLines)->not->toBeEmpty();
    $firstPer = array_values($perLines)[0];
    expect(trim($field($firstPer, 20, 45)))->toBe('SINGER');
});
