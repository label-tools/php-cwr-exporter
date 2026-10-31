<?php

use LabelTools\PhpCwrExporter\CwrBuilder;

function chainPublisher(string $ip, string $type, bool $controlled = true, array $shares = [0, 0, 0]): array
{
    return [
        'interested_party_number' => $ip,
        'name' => "PUBLISHER {$ip}",
        'type' => $type,
        'ipi_name_number' => '00000000123',
        'controlled' => $controlled,
        'pr_ownership_share' => $shares[0],
        'mr_ownership_share' => $shares[1],
        'sr_ownership_share' => $shares[2],
    ];
}

function chainExport(string $version, array $publishers, string $writerPublisher = 'P1'): array
{
    $builder = CwrBuilder::$version()
        ->senderType('PB')
        ->senderId('123456789')
        ->senderName('ACME')
        ->works([[
            'submitter_work_number' => 'W1',
            'title' => 'SONG',
            'title_type' => 'AT',
            'distribution_category' => 'POP',
            'version_type' => 'ORI',
            'publishers' => $publishers,
            'writers' => [[
                'interested_party_number' => 'W1',
                'last_name' => 'DOE',
                'designation_code' => 'CA',
                'pr_ownership_share' => 50,
                'publisher_interested_party_number' => $writerPublisher,
            ]],
        ]]);

    $lines = explode("\r\n", trim($builder->export()));
    expect($builder->getSkippedWorks())->toBe([]);

    return $lines;
}

/** @return array<int, string> record type + publisher sequence + IP number */
function publisherLines(array $lines): array
{
    $publishers = array_filter($lines, fn ($line) => in_array(substr($line, 0, 3), ['SPU', 'OPU'], true));

    return array_values(array_map(fn ($line) => substr($line, 0, 3) . ' ' . substr($line, 19, 2) . ' ' . rtrim(substr($line, 21, 9)), $publishers));
}

describe('publisher chains', function () {
    it('gives an administrator the sequence of its original publisher', function (string $version) {
        $lines = chainExport($version, [
            chainPublisher('P1', 'E', true, [25, 50, 50]),
            chainPublisher('O1', 'E', false, [25, 50, 50]),
            chainPublisher('O2', 'AM', false),
        ]);

        expect(publisherLines($lines))->toBe(['SPU 01 P1', 'OPU 02 O1', 'OPU 02 O2']);
    })->with(['v21', 'v22']);

    it('keeps a sub-publisher in the chain of its original publisher', function (string $version) {
        $lines = chainExport($version, [
            chainPublisher('P1', 'E', true, [50, 100, 100]),
            chainPublisher('P2', 'SE'),
        ]);

        expect(publisherLines($lines))->toBe(['SPU 01 P1', 'SPU 01 P2']);
    })->with(['v21', 'v22']);

    it('allows one original publisher per chain', function (string $version) {
        $lines = chainExport($version, [
            chainPublisher('P1', 'E', true, [25, 50, 50]),
            chainPublisher('P2', 'SE'),
            chainPublisher('P3', 'E', true, [25, 50, 50]),
        ]);

        expect(publisherLines($lines))->toBe(['SPU 01 P1', 'SPU 01 P2', 'SPU 02 P3']);
    })->with(['v21', 'v22']);

    it('emits controlled chains before uncontrolled chains', function (string $version) {
        $lines = chainExport($version, [
            chainPublisher('O1', 'E', false, [25, 50, 50]),
            chainPublisher('P1', 'E', true, [25, 50, 50]),
        ]);

        expect(publisherLines($lines))->toBe(['SPU 01 P1', 'OPU 02 O1']);
    })->with(['v21', 'v22']);

    it('does not join an uncontrolled administrator to a controlled chain', function () {
        $lines = chainExport('v21', [
            chainPublisher('P1', 'E', true, [50, 100, 100]),
            chainPublisher('O1', 'AM', false),
        ]);

        expect(publisherLines($lines))->toBe(['SPU 01 P1', 'OPU 02 O1']);
    });

    it('writes the chain sequence on the v2.2 PWR record', function () {
        $lines = chainExport('v22', [
            chainPublisher('O1', 'E', false, [25, 50, 50]),
            chainPublisher('P1', 'E', true, [25, 50, 50]),
        ]);

        $pwr = array_values(array_filter($lines, fn ($line) => str_starts_with($line, 'PWR')))[0];

        expect(substr($pwr, 110, 2))->toBe('01');
    });
});

describe('publisher chain order validation', function () {
    $territory = fn (float $pr, float $other) => [['tis_code' => '2136', 'pr_collection_share' => $pr, 'mr_collection_share' => $other, 'sr_collection_share' => $other]];

    it('accepts a second chain after an SPT', function (string $version) use ($territory) {
        $lines = chainExport($version, [
            chainPublisher('P1', 'E', true, [25, 50, 50]) + ['territories' => $territory(25, 50)],
            chainPublisher('P2', 'E', true, [25, 50, 50]) + ['territories' => $territory(25, 50)],
        ]);

        expect(array_map(fn ($line) => substr($line, 0, 3), array_slice($lines, 3, 4)))->toBe(['SPU', 'SPT', 'SPU', 'SPT'])
            ->and(publisherLines($lines))->toBe(['SPU 01 P1', 'SPU 02 P2']);
    })->with(['v21', 'v22']);

    it('accepts a sub-publisher after the original publisher SPT', function (string $version) use ($territory) {
        $lines = chainExport($version, [
            chainPublisher('P1', 'E', true, [50, 100, 100]) + ['territories' => $territory(50, 100)],
            chainPublisher('P2', 'SE'),
        ]);

        expect(publisherLines($lines))->toBe(['SPU 01 P1', 'SPU 01 P2']);
    })->with(['v21', 'v22']);
});
