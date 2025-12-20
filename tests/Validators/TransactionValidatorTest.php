<?php

use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Enums\MusicalWorkDistributionCategory;
use LabelTools\PhpCwrExporter\Enums\PublisherType;
use LabelTools\PhpCwrExporter\Enums\TitleType;
use LabelTools\PhpCwrExporter\Enums\VersionType;
use LabelTools\PhpCwrExporter\Enums\WriterDesignation;
use LabelTools\PhpCwrExporter\Validators\TransactionValidator;

function makeWork(array $overrides = []): WorkDefinition
{
    $base = [
        'submitter_work_number' => 'WORK001',
        'title' => 'TITLE',
        'title_type' => TitleType::ORIGINAL_TITLE,
        'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
        'version_type'=> VersionType::ORIGINAL_WORK,
        'writers' => [[
            'interested_party_number' => 'W000001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
            'publisher_interested_party_number' => 'P000001',
            'pr_ownership_share' => 50,
            'mr_ownership_share' => 50,
            'sr_ownership_share' => 50,
            'territories' => [[
                'tis_code' => '213',
                'inclusion_exclusion_indicator' => 'I',
                'pr_collection_share' => 50,
                'mr_collection_share' => 50,
                'sr_collection_share' => 50,
            ]],
        ]],
        'publishers' => [[
            'interested_party_number' => 'P000001',
            'name' => 'Publishing Company',
            'type' => PublisherType::ORIGINAL_PUBLISHER->value,
            'ipi_name_number' => '123456789',
            'pr_ownership_share' => 50,
            'mr_ownership_share' => 50,
            'sr_ownership_share' => 50,
            'territories' => [[
                'tis_code' => '213',
                'inclusion_exclusion_indicator' => 'I',
                'pr_collection_share' => 50,
                'mr_collection_share' => 50,
                'sr_collection_share' => 50,
            ]],
        ]],
    ];

    $data = array_replace_recursive($base, $overrides);

    return WorkDefinition::fromArray($data);
}

describe('TransactionValidator', function () {
    it('fails any writer PR chain that cannot reach a 100% total', function () {
        $validator = new TransactionValidator();
        $work = makeWork([
            'writers' => [[
                'interested_party_number' => 'WLOW',
                'first_name' => 'Low',
                'last_name' => 'Share',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'publisher_interested_party_number' => 'P000001',
                'pr_ownership_share' => 30, // below 50%
                'territories' => [[
                    'tis_code' => '213',
                    'pr_collection_share' => 10.0,
                ]],
            ]],
        ]);

        expect(fn () => $validator->validate($work))
            ->toThrow(InvalidArgumentException::class, 'Total combined PR ownership share must be 0% or 100%');
    });

    it('fails when collection shares exceed 100% for a territory', function () {
        $validator = new TransactionValidator();
        $work = makeWork([
            'writers' => [[
                'interested_party_number' => 'W000001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'publisher_interested_party_number' => 'P000001',
                'pr_ownership_share' => 60,
                'mr_ownership_share' => 60,
                'sr_ownership_share' => 60,
                'territories' => [[
                    'tis_code' => '213',
                    'pr_collection_share' => 60.0, // writer
                ]],
            ]],
            'publishers' => [[
                'interested_party_number' => 'P000001',
                'name' => 'Publishing Company',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 40,
                'mr_ownership_share' => 40,
                'sr_ownership_share' => 40,
                'territories' => [[
                    'tis_code' => '213',
                    'pr_collection_share' => 50.0, // publisher
                ]],
            ]],
        ]);

        expect(fn () => $validator->validate($work))
            ->toThrow(InvalidArgumentException::class, 'collection share');
    });

    it('allows only one original publisher', function () {
        $validator = new TransactionValidator();
        $work = makeWork([
            'publishers' => [
                [
                    'interested_party_number' => 'P000001',
                    'name' => 'Publishing Company',
                    'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                    'ipi_name_number' => '123456789',
                ],
                [
                    'interested_party_number' => 'P000002',
                    'name' => 'Second Original',
                    'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                    'ipi_name_number' => '123456789',
                ],
            ],
        ]);

        expect(fn () => $validator->validate($work))
            ->toThrow(InvalidArgumentException::class, 'Original Publisher');
    });

    it('requires at least one writer with designation CA/A/C', function () {
        $validator = new TransactionValidator();
        $work = makeWork([
            'writers' => [[
                'interested_party_number' => 'W000111',
                'first_name' => 'Ann',
                'last_name' => 'Adaptor',
                'designation_code' => WriterDesignation::ADAPTOR->value,
                'publisher_interested_party_number' => 'P000001',
                'territories' => [[
                    'tis_code' => '213',
                    'pr_collection_share' => 10.0,
                ]],
            ]],
        ]);

        expect(fn () => $validator->validate($work))
            ->toThrow(InvalidArgumentException::class, 'designation CA, A, or C');
    });

    it('requires controlled publishers to appear before uncontrolled ones', function () {
        $validator = new TransactionValidator();
        $work = makeWork([
            'publishers' => [
                [
                    'interested_party_number' => 'P000002',
                    'controlled' => false,
                    'name' => '',
                    'type' => null,
                    'ipi_name_number' => '',
                    'territories' => null,
                    'pr_ownership_share' => 0,
                    'mr_ownership_share' => 0,
                    'sr_ownership_share' => 0,
                ],
                [
                    'interested_party_number' => 'P000001',
                    'name' => 'Publishing Company',
                    'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                    'ipi_name_number' => '123456789',
                    'pr_ownership_share' => 50,
                    'mr_ownership_share' => 50,
                    'sr_ownership_share' => 50,
                    'territories' => [[
                        'tis_code' => '213',
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 50.0,
                        'mr_collection_share' => 50.0,
                        'sr_collection_share' => 50.0,
                    ]],
                ],
            ],
        ]);

        expect(fn () => $validator->validate($work))->toThrow(InvalidArgumentException::class, 'per CWR ordering rules');
    });

    it('rejects territories for uncontrolled publishers because SPT cannot follow an OPU', function () {
        $validator = new TransactionValidator();
        $work = makeWork([
            'publishers' => [[
                'interested_party_number' => 'P-OPU',
                'name' => '',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '',
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 50,
                'sr_ownership_share' => 50,
                'controlled' => false,
                'territories' => [[
                    'tis_code' => '213',
                    'pr_collection_share' => 10,
                    'mr_collection_share' => 10,
                    'sr_collection_share' => 10,
                ]],
            ]],
        ]);

        expect(fn () => $validator->validate($work))
            ->toThrow(InvalidArgumentException::class, 'SPT records cannot be used with OPU records');
    });

    it('requires combined ownership totals to be 0% or exactly 100%', function () {
        $validator = new TransactionValidator();
        $work = makeWork([
            'writers' => [[
                'interested_party_number' => 'W000009',
                'first_name' => 'One',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'publisher_interested_party_number' => 'P000001',
                'pr_ownership_share' => 30,
                'mr_ownership_share' => 30,
                'sr_ownership_share' => 30,
            ]],
            'publishers' => [[
                'interested_party_number' => 'P000001',
                'name' => 'Publishing Company',
                'type' => PublisherType::ORIGINAL_PUBLISHER->value,
                'ipi_name_number' => '123456789',
                'pr_ownership_share' => 30,
                'mr_ownership_share' => 30,
                'sr_ownership_share' => 30,
            ]],
        ]);

        expect(fn () => $validator->validate($work))
            ->toThrow(InvalidArgumentException::class, 'Total combined PR ownership share must be 0% or 100%');
    });

});
