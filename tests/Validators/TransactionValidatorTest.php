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
                'pr_collection_share' => 50.0,
                'mr_collection_share' => 50.0,
                'sr_collection_share' => 50.0,
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
                'pr_collection_share' => 50.0,
                'mr_collection_share' => 50.0,
                'sr_collection_share' => 50.0,
            ]],
        ]],
    ];

    $data = array_replace_recursive($base, $overrides);

    return WorkDefinition::fromArray($data);
}

describe('TransactionValidator', function () {
    it('requires writer ownership totals to be 0% or >= 50% for PR', function () {
        $validator = new TransactionValidator();
        $work = makeWork([
            'writers' => [[
                'interested_party_number' => 'WLOW',
                'first_name' => 'Low',
                'last_name' => 'Share',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'publisher_interested_party_number' => 'P000001',
                'pr_ownership_share' => 30, // below 50%
                'mr_ownership_share' => 10,
                'sr_ownership_share' => 10,
                'territories' => [[
                    'tis_code' => '213',
                    'pr_collection_share' => 10.0,
                ]],
            ]],
        ]);

        expect(fn () => $validator->validate($work))
            ->toThrow(InvalidArgumentException::class, 'writer PR ownership share');
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
                'pr_ownership_share' => 50,
                'mr_ownership_share' => 100,
                'sr_ownership_share' => 100,
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
});
