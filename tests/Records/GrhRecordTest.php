<?php

use LabelTools\PhpCwrExporter\Records\GrhRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\GrhRecord as V21GrhRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrhRecord as V22GrhRecord;

describe('Group Header Record', function () {
    describe('Base', function () {
        it('builds a valid GRH record according to the spec', function () {
            $record = new GrhRecord(
                transactionType: 'NWR',
                groupId: 1,
            );

            $record = $record->toString();

            expect(strlen($record))->toBe(11);
            expect(substr($record, 0, 3))->toBe('GRH');
            expect(substr($record, 3, 3))->toBe('NWR');
            expect(substr($record, 6, 11))->toBe('00001');

        });

        it('builds a valid GRH record according to the spec via fluent', function () {
            $record = (new GrhRecord())
                ->setTransactionType('NWR')
                ->setGroupId(1);

            $record = $record->toString();

            expect(strlen($record))->toBe(11);
            expect(substr($record, 0, 3))->toBe('GRH');
            expect(substr($record, 3, 3))->toBe('NWR');
            expect(substr($record, 6, 11))->toBe('00001');
        });

        it('throws when transaction type is not exactly 3 characters', function () {
            new GrhRecord(transactionType: 'NW', groupId: 1);
        })->throws(InvalidArgumentException::class);

        // -- Invalid transaction type not recognized --
        it('throws when transaction type does not exist', function () {
            new GrhRecord(transactionType: 'ABC', groupId: 1);
        })->throws(InvalidArgumentException::class);

        it('throws when group id is below minimum', function () {
            new GrhRecord(transactionType: 'NWR', groupId: 0);
        })->throws(InvalidArgumentException::class);

        it('throws when group id is above maximum', function () {
            new GrhRecord(transactionType: 'NWR', groupId: 100000);
        })->throws(InvalidArgumentException::class);

        // -- Padding edge case --
        it('pads multi-digit group id correctly', function () {
            $record = new GrhRecord(transactionType: 'NWR', groupId: 123);
            expect(substr($record->toString(), 6, 5))->toBe('00123');
        });

    });

    describe('CWR v2.1', function () {
        it('builds a valid GRH record for CWR v2.1', function () {
            $record = (new V21GrhRecord())
                ->setTransactionType('NWR')
                ->setGroupId(1);

            $record = $record->toString();

            expect(strlen($record))->toBe(28); // 3 + 3 + 5 + 5 + 10 + 2
            expect(substr($record, 0, 3))->toBe('GRH');
            expect(substr($record, 3, 3))->toBe('NWR');
            expect(substr($record, 6, 5))->toBe('00001');
            expect(substr($record, 11, 5))->toBe('02.10');
            expect(substr($record, 26, 2))->toBe('  ');
        });
    });

    describe('CWR v2.2', function () {
        it('builds a valid GRH record for CWR v2.2', function () {
            $record = (new V22GrhRecord())
                ->setTransactionType('NWR')
                ->setGroupId(1)
                ->setBatchRequest(123);

            $record = $record->toString();

            expect(strlen($record))->toBe(28); // 3 + 3 + 5 + 5 + 10 + 2
            expect(substr($record, 0, 3))->toBe('GRH');
            expect(substr($record, 3, 3))->toBe('NWR');
            expect(substr($record, 6, 5))->toBe('00001');
            expect(substr($record, 11, 5))->toBe('02.20');
            expect(substr($record, 16, 10))->toBe('123       ');
            expect(substr($record, 26, 2))->toBe('  ');
        });
    });
});