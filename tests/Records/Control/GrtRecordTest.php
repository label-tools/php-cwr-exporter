

<?php

use LabelTools\PhpCwrExporter\Records\Control\GrtRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\GrtRecord as V21GrtRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\Control\GrtRecord as V22GrtRecord;

describe('Group Trailer Record', function () {
    describe('Base', function () {
        it('builds a valid GRT record according to the spec', function () {
            $record = new GrtRecord(
                groupId: 1,
                transactionCount: 2,
                recordCount: 3
            );
            $string = $record->toString();

            expect(strlen($string))->toBe(24); // 3 + 5 + 8 + 8
            expect(substr($string, 0, 3))->toBe('GRT');
            expect(substr($string, 3, 5))->toBe('00001');
            expect(substr($string, 8, 8))->toBe('00000002');
            expect(substr($string, 16, 8))->toBe('00000003');
        });

        it('builds a valid GRT record via fluent interface', function () {
            $string = (new GrtRecord())
                ->setGroupId(1)
                ->setTransactionCount(2)
                ->setRecordCount(3)
                ->toString();

            expect(strlen($string))->toBe(24);
            expect(substr($string, 0, 3))->toBe('GRT');
            expect(substr($string, 3, 5))->toBe('00001');
            expect(substr($string, 8, 8))->toBe('00000002');
            expect(substr($string, 16, 8))->toBe('00000003');
        });

        it('throws when groupId is below minimum', function () {
            new GrtRecord(groupId: 0, transactionCount: 1, recordCount: 1);
        })->throws(InvalidArgumentException::class);

        it('throws when groupId is above maximum', function () {
            new GrtRecord(groupId: 100000, transactionCount: 1, recordCount: 1);
        })->throws(InvalidArgumentException::class);

        it('throws when transactionCount is negative', function () {
            new GrtRecord(groupId: 1, transactionCount: -1, recordCount: 1);
        })->throws(InvalidArgumentException::class);

        it('throws when transactionCount exceeds maximum', function () {
            new GrtRecord(groupId: 1, transactionCount: 100000000, recordCount: 1);
        })->throws(InvalidArgumentException::class);

        it('throws when recordCount is negative', function () {
            new GrtRecord(groupId: 1, transactionCount: 1, recordCount: -1);
        })->throws(InvalidArgumentException::class);

        it('throws when recordCount exceeds maximum', function () {
            new GrtRecord(groupId: 1, transactionCount: 1, recordCount: 100000000);
        })->throws(InvalidArgumentException::class);

        it('pads multi-digit fields correctly', function () {
            $string = (new GrtRecord())
                ->setGroupId(123)
                ->setTransactionCount(4567)
                ->setRecordCount(89012)
                ->toString();

            expect(substr($string, 3, 5))->toBe('00123');
            expect(substr($string, 8, 8))->toBe('00004567');
            expect(substr($string, 16, 8))->toBe('00089012');
        });
    });

    describe('CWR v2.1', function () {
        it('builds a valid GRT record for CWR v2.1', function () {
            $record = (new V21GrtRecord())
                ->setGroupId(1);

            $record = $record->toString();

            expect(strlen($record))->toBe(24);

        });
    });

    describe('CWR v2.2', function () {
        it('builds a valid GRT record for CWR v2.2', function () {
            $record = (new V22GrtRecord())
                ->setGroupId(1);

            $record = $record->toString();

            expect(strlen($record))->toBe(24);

        });
    });
});