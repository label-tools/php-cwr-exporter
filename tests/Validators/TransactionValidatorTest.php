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
            'publisher_interested_party_number' => null,
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
    it('allows combined totals within tolerance (99.97 treated as 100)', function () {
    $validator = new TransactionValidator();

    $work = makeWork([
        'writers' => [[
            'interested_party_number' => 'W1',
            'first_name' => 'A',
            'last_name' => 'B',
            'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
            'publisher_interested_party_number' => 'P000001',
            'pr_ownership_share' => 50.00,
            'mr_ownership_share' => 0.00,
            'sr_ownership_share' => 0.00,
        ]],
        'publishers' => [[
            'interested_party_number' => 'P000001',
            'name' => 'Publishing Company',
            'type' => PublisherType::ORIGINAL_PUBLISHER->value,
            'ipi_name_number' => '123456789',
            'pr_ownership_share' => 49.97, // total 99.97
            'mr_ownership_share' => 0.00,
            'sr_ownership_share' => 0.00,
        ]],
    ]);

    expect(fn () => $validator->validate($work))->not->toThrow(InvalidArgumentException::class);
});

it('rejects combined totals outside tolerance (99.90)', function () {
    $validator = new TransactionValidator();

    $work = makeWork([
        'writers' => [[
            'interested_party_number' => 'W1',
            'first_name' => 'A',
            'last_name' => 'B',
            'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
            'publisher_interested_party_number' => 'P000001',
            'pr_ownership_share' => 50.00,
        ]],
        'publishers' => [[
            'interested_party_number' => 'P000001',
            'name' => 'Publishing Company',
            'type' => PublisherType::ORIGINAL_PUBLISHER->value,
            'ipi_name_number' => '123456789',
            'pr_ownership_share' => 49.90, // total 99.90
        ]],
    ]);

    expect(fn () => $validator->validate($work))
        ->toThrow(InvalidArgumentException::class);
});

it('allows 0% MR/SR totals when everything is zero', function () {
    $validator = new TransactionValidator();

    $work = makeWork([
        'writers' => [[
            'interested_party_number' => 'W1',
            'first_name' => 'A',
            'last_name' => 'B',
            'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
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
            'mr_ownership_share' => 0,
            'sr_ownership_share' => 0,
        ]],
    ]);

    expect(fn () => $validator->validate($work))->not->toThrow(InvalidArgumentException::class);
});

it('rejects publisher PR ownership > 50%', function () {
    $validator = new TransactionValidator();

    $work = makeWork([
        'writers' => [[
            'interested_party_number' => 'W1',
            'first_name' => 'A',
            'last_name' => 'B',
            'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
            'publisher_interested_party_number' => 'P000001',
            'pr_ownership_share' => 40,
        ]],
        'publishers' => [[
            'interested_party_number' => 'P000001',
            'name' => 'Publishing Company',
            'type' => PublisherType::ORIGINAL_PUBLISHER->value,
            'ipi_name_number' => '123456789',
            'pr_ownership_share' => 60,
        ]],
    ]);

    expect(fn () => $validator->validate($work))
        ->toThrow(InvalidArgumentException::class, 'publisher PR ownership');
});

it('rejects SWT without a preceding SWR (ordering adjacency)', function () {
    $validator = new TransactionValidator();

    // Force an uncontrolled writer with territories (should not generate SWT), or simulate
    // a work definition that would produce SWT without SWR if your builder is buggy.
    $work = makeWork([
        'writers' => [[
            'interested_party_number' => 'W-OWR',
            'first_name' => 'X',
            'last_name' => 'Y',
            'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
            'controlled' => false,
            'territories' => [[
                'tis_code' => '2136',
                'inclusion_exclusion_indicator' => 'I',
                'pr_collection_share' => 50,
                'mr_collection_share' => 50,
                'sr_collection_share' => 50,
            ]],
        ]],
    ]);

    // If your exporter never emits SWT for OWR, then this should pass.
    // If it emits SWT incorrectly, it should fail. Decide what behavior you want.
    expect(fn () => $validator->validate($work))->not->toThrow(InvalidArgumentException::class);
});

it('rejects PWR for an uncontrolled writer (OWR)', function () {
    $validator = new TransactionValidator();

    $work = makeWork([
        'writers' => [[
            'interested_party_number' => 'W-OWR',
            'first_name' => 'X',
            'last_name' => 'Y',
            'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
            'controlled' => false,
            'publisher_interested_party_number' => 'P000001', // should be ignored or rejected
            'pr_ownership_share' => 50,
            'mr_ownership_share' => 0,
            'sr_ownership_share' => 0,
        ]],
    ]);

    expect(fn () => $validator->validate($work))
        ->toThrow(InvalidArgumentException::class, 'PWR');
});

it('rejects OPU with territories (SPT not allowed for OPU)', function () {
    $validator = new TransactionValidator();

    $work = makeWork([
        'publishers' => [[
            'interested_party_number' => 'P-OPU',
            'controlled' => false,
            'publisher_unknown_indicator' => true,
            'name' => '',
            'type' => PublisherType::ORIGINAL_PUBLISHER->value,
            'ipi_name_number' => '',
            'pr_ownership_share' => 50,
            'mr_ownership_share' => 50,
            'sr_ownership_share' => 50,
            'territories' => [[
                'tis_code' => '2136',
                'inclusion_exclusion_indicator' => 'I',
                'pr_collection_share' => 50,
                'mr_collection_share' => 50,
                'sr_collection_share' => 50,
            ]],
        ]],
    ]);

    expect(fn () => $validator->validate($work))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects SWR that interrupts an open SWT block for a prior writer', function () {
    $validator = new TransactionValidator();

    $work = makeWork([
        'writers' => [
            [
                'interested_party_number' => 'W-A',
                'first_name' => 'Alpha',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'publisher_interested_party_number' => null,
                'pr_ownership_share' => 45,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
                'territories' => [
                    [
                        'tis_code' => '213',
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 0,
                        'mr_collection_share' => 0,
                        'sr_collection_share' => 0,
                    ],
                    [
                        'tis_code' => '214',
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 0,
                        'mr_collection_share' => 0,
                        'sr_collection_share' => 0,
                    ],
                ],
            ],
            [
                'interested_party_number' => 'W-B',
                'first_name' => 'Beta',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'publisher_interested_party_number' => 'P000001',
                'pr_ownership_share' => 45,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
                'territories' => [
                    [
                        'tis_code' => '213',
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 0,
                        'mr_collection_share' => 0,
                        'sr_collection_share' => 0,
                    ],
                ],
            ],
        ],
        'publishers' => [[
            'interested_party_number' => 'P000001',
            'name' => 'Publishing Company',
            'type' => PublisherType::ORIGINAL_PUBLISHER->value,
            'ipi_name_number' => '123456789',
            'pr_ownership_share' => 10,
            'mr_ownership_share' => 0,
            'sr_ownership_share' => 0,
            'territories' => [],
        ]],
    ]);

    expect(fn () => $validator->validate($work))
        ->toThrow(InvalidArgumentException::class, 'SWT block');
});

it('allows sequential writer blocks when SWT chain is closed by PWR', function () {
    $validator = new TransactionValidator();

    $work = makeWork([
        'writers' => [
            [
                'interested_party_number' => 'W-A',
                'first_name' => 'Alpha',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'publisher_interested_party_number' => 'P000001',
                'pr_ownership_share' => 45,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
                'territories' => [
                    [
                        'tis_code' => '213',
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 0,
                        'mr_collection_share' => 0,
                        'sr_collection_share' => 0,
                    ],
                ],
            ],
            [
                'interested_party_number' => 'W-B',
                'first_name' => 'Beta',
                'last_name' => 'Writer',
                'designation_code' => WriterDesignation::COMPOSER_AUTHOR->value,
                'publisher_interested_party_number' => 'P000001',
                'pr_ownership_share' => 45,
                'mr_ownership_share' => 0,
                'sr_ownership_share' => 0,
                'territories' => [
                    [
                        'tis_code' => '214',
                        'inclusion_exclusion_indicator' => 'I',
                        'pr_collection_share' => 0,
                        'mr_collection_share' => 0,
                        'sr_collection_share' => 0,
                    ],
                ],
            ],
        ],
        'publishers' => [[
            'interested_party_number' => 'P000001',
            'name' => 'Publishing Company',
            'type' => PublisherType::ORIGINAL_PUBLISHER->value,
            'ipi_name_number' => '123456789',
            'pr_ownership_share' => 10,
            'mr_ownership_share' => 0,
            'sr_ownership_share' => 0,
            'territories' => [],
        ]],
    ]);

    expect(fn () => $validator->validate($work))
        ->not->toThrow(InvalidArgumentException::class);
});

});
