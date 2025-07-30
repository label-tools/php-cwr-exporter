<?php

use LabelTools\PhpCwrExporter\CwrBuilder;
use LabelTools\PhpCwrExporter\Enums\LanguageCode;
use LabelTools\PhpCwrExporter\Enums\MusicalWorkDistributionCategory;
use LabelTools\PhpCwrExporter\Enums\PublisherType;
use LabelTools\PhpCwrExporter\Enums\SenderType;
use LabelTools\PhpCwrExporter\Enums\SocietyCode;
use LabelTools\PhpCwrExporter\Enums\TisCode;
use LabelTools\PhpCwrExporter\Enums\TitleType;
use LabelTools\PhpCwrExporter\Enums\TransactionType;
use LabelTools\PhpCwrExporter\Enums\VersionType;
use LabelTools\PhpCwrExporter\Enums\WriterDesignation;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrhRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrtRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\NwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\PwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SpuRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SptRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SwtRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\TrlRecord;

it('builds a CWR 2.2 with one New Registration Work', function () {
    $cwr = CwrBuilder::v22()
        ->senderType(SenderType::PUBLISHER)
        ->senderId('01265713057')
        ->senderName('WAYU Publishing')
        ->software('LabelTools CWR Exporter', '1.0.0')
        ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
        ->works([
            [
                'submitter_work_number' => '00000001',
                'title' => 'STAY',
                'title_type' => TitleType::ORIGINAL_TITLE,
                'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
                'version_type'=> VersionType::ORIGINAL_WORK,
                'iswc' => 'T1234567890',
                'writers' => [
                [
                    'interested_party_number' => 'W000001',
                    'first_name' => 'Leonardo',
                    'last_Name' => 'Ortegon',
                    'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                    'ipi_name_number' => '123456789',
                    'pr_affiliation_society' => SocietyCode::BMI->value,
                    'territories' => [
                        [
                            'tis_code' => TisCode::WORLD->value,
                            'inclusion_exclusion_indicator' => 'I',
                        ]
                    ]
                ]
            ],

            'publishers' => [
                [
                    'interested_party_number' => 'P000001',
                    'name' => 'WAYU Publishing',
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
                    'territories' => [
                        [
                            'tis_code' => TisCode::WORLD->value,
                            'inclusion_exclusion_indicator' => 'I',
                        ]
                    ]
                ]
            ]
            ]
        ]);

    var_dump($cwr->export());
});

// it('emits a CWR skeleton in the correct order', function () {
//     $hdr = new HdrRecord(
//         senderType: SenderType::PUBLISHER->value,
//         senderId: '123456789',
//         senderName: 'LabelTools Publisher',
//     );

//     $grh = new GrhRecord(
//         transactionType: TransactionType::NEW_WORK_REGISTRATION
//     );

//     $nwr = new NwrRecord(
//         workTitle: 'My Song Title',
//         submitterWorkNumber: 'WORK000001',
//         mwDistributionCategory: MusicalWorkDistributionCategory::POPULAR->value,
//         versionType: VersionType::ORIGINAL_WORK->value
//     );

//     $spu = new SpuRecord(
//         publisherSequence: 1,
//         interestedPartyNumber: '1',
//         publisherName: 'LabelTools Publisher',
//         publisherType: PublisherType::ORIGINAL_PUBLISHER->value,
//         publisherIpiName: '123456789'
//     );

//     $spt = new SptRecord(
//         interestedPartyNumber: '1',
//         prCollectionShare: 50,
//         mrCollectionShare: 100,
//         srCollectionShare: 100,
//         inclusionExclusionIndicator: 'I',
//         tisNumericCode: TisCode::WORLD->value,
//     );

//     $swr = new SwrRecord(
//         interestedPartyNumber: '1',
//         writerLastName: 'Smith',
//         writerFirstName: 'John',
//         writerDesignationCode: WriterDesignation::COMPOSER_AUTHOR
//     );

//     $swt = new SwtRecord(
//         interestedPartyNumber: '1',
//         inclusionExclusionIndicator: 'I',
//         tisNumericCode: TisCode::WORLD->value,
//     );

//     $pwr = new PwrRecord(
//         publisherIpNumber: '123456789',
//         publisherName: 'LabelTools Publisher',
//     );

//     $grt = new GrtRecord(
//         groupId: 1,
//         transactionCount: 1,
//         recordCount: 1,
//     );

//     $trl = new TrlRecord(
//         groupCount: 1,
//         transactionCount: 1,
//         recordCount: 1,
//     );

//     $lines = [
//         $hdr->toString(),
//         $grh->toString(),
//         $nwr->toString(),
//         $spu->toString(),
//         $spt->toString(),
//         $swr->toString(),
//         $swt->toString(),
//         $pwr->toString(),
//         $grt->toString(),
//         $trl->toString(),
//     ];

//     expect($lines)->toHaveCount(10);
//     expect($lines[0])->toStartWith('HDR');
//     expect($lines[1])->toStartWith('GRH');
//     expect($lines[2])->toStartWith('NWR');
//     expect($lines[3])->toStartWith('SPU');
//     expect($lines[4])->toStartWith('SPT');
//     expect($lines[5])->toStartWith('SWR');
//     expect($lines[6])->toStartWith('SWT');
//     expect($lines[7])->toStartWith('PWR');
//     expect($lines[8])->toStartWith('GRT');
//     expect($lines[9])->toStartWith('TRL');
// });