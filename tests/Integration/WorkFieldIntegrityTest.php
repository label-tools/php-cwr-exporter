<?php

use LabelTools\PhpCwrExporter\CwrBuilder;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Records\Detail\PerRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SwrRecord;

function integrityWork(array $overrides = [], array $publisher = []): array
{
    return $overrides + [
        'submitter_work_number' => 'W1',
        'title' => 'SONG',
        'title_type' => 'AT',
        'distribution_category' => 'POP',
        'version_type' => 'ORI',
        'publishers' => [$publisher + [
            'interested_party_number' => 'P1',
            'name' => 'PUB',
            'type' => 'E',
            'ipi_name_number' => '00000000123',
            'pr_ownership_share' => 50,
            'mr_ownership_share' => 100,
            'sr_ownership_share' => 100,
        ]],
        'writers' => [[
            'interested_party_number' => 'W1',
            'last_name' => 'DOE',
            'designation_code' => 'CA',
            'pr_ownership_share' => 50,
            'publisher_interested_party_number' => 'P1',
        ]],
    ];
}

function integrityBuilder(string $version, array $works): CwrBuilder
{
    return CwrBuilder::$version()->senderType('PB')->senderId('123456789')->senderName('ACME')->works($works);
}

describe('recorded indicator', function () {
    it('keeps the indicator through export', function (mixed $input, string $expected) {
        $output = integrityBuilder('v21', [integrityWork(['recorded' => $input])])->export();
        $nwr = explode("\r\n", $output)[2];

        expect($nwr[135])->toBe($expected);
    })->with([
        ['Y', 'Y'], ['N', 'N'], ['U', 'U'], ['n', 'N'], ['u', 'U'],
        [true, 'Y'], [false, 'N'], ['1', 'Y'], ['0', 'N'], [1, 'Y'], [0, 'N'], ['', 'N'],
    ]);

    it('defaults to N when not provided', function () {
        expect(WorkDefinition::fromArray(integrityWork())->recorded)->toBeFalse();
    });

    it('skips a work with an invalid indicator', function () {
        $builder = integrityBuilder('v21', [integrityWork(['recorded' => 'MAYBE'])]);
        $builder->export();

        expect($builder->getSkippedWorks()[0]['error'])->toContain('Recorded Indicator');
    });
});

describe('field width protection', function () {
    it('skips a work whose field would overflow and keeps the rest', function (string $version, array $work, string $field) {
        $builder = integrityBuilder($version, [$work, integrityWork(['submitter_work_number' => 'W2'])]);
        $output = $builder->export();

        expect($builder->getSkippedWorks())->toHaveCount(1)
            ->and($builder->getSkippedWorks()[0]['error'])->toContain($field)
            ->and(substr_count($output, "\r\nNWR"))->toBe(1)
            ->and($output)->toContain('W2');
    })->with([
        'work number v21' => ['v21', integrityWork(['submitter_work_number' => str_repeat('9', 15)]), 'SUBMITTER_WORK_NUMBER'],
        'work number v22' => ['v22', integrityWork(['submitter_work_number' => str_repeat('9', 15)]), 'SUBMITTER_WORK_NUMBER'],
        'publisher ipi' => ['v21', integrityWork([], ['ipi_name_number' => str_repeat('1', 12)]), 'PUBLISHER_IPI_NAME'],
        'publisher tax id' => ['v21', integrityWork([], ['tax_id' => str_repeat('1', 10)]), 'TAX_ID'],
    ]);

    it('accepts values at exactly the field width', function () {
        $builder = integrityBuilder('v21', [integrityWork(['submitter_work_number' => str_repeat('9', 14)])]);
        $builder->export();

        expect($builder->getSkippedWorks())->toBe([]);
    });

    it('keeps a maximum width field at the full record length', function () {
        $record = (new PerRecord(str_repeat('A', 45)))->setRecordPrefix(0, 1);

        expect(strlen($record->toString()))->toBe(118);
    });

    it('rejects oversized numeric fields', function () {
        $record = new SwrRecord('W1', 'DOE', '', 'CA');
        $record->setRecordPrefix(0, 1);
        (fn () => $this->data[static::IDX_PR_OWNERSHIP_SHARE] = 123456)->call($record);

        expect(fn () => $record->toString())->toThrow(InvalidArgumentException::class, 'PR_OWNERSHIP_SHARE');
    });
});
