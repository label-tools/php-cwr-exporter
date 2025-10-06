

<?php

use LabelTools\PhpCwrExporter\Records\SpuRecord;

describe('SPU (Publisher Controlled) Record', function () {
    describe('Minimal required fields and padding', function () {
        it('builds a minimal valid SPU record', function () {
            $rec = new SpuRecord(1,'IP00001','MyPub','E');

            $str = $rec->setRecordPrefix(0, 0)->toString();
            expect(strlen($str))->toBe(139);

            // Record Prefix (19 A)
            expect(substr($str, 0, 19))->toBe(str_pad('SPU0000000000000000', 19, ' '));
            // Publisher Sequence # (2 N)
            expect(substr($str, 19, 2))->toBe('01');
            // Interested Party # (9 A)
            expect(substr($str, 21, 9))->toBe(str_pad('IP00001', 9, ' '));
            // Publisher Name (45 A)
            expect(substr($str, 30, 45))->toBe(str_pad('MyPub', 45, ' '));
            // Publisher Unknown Indicator (must be blank)
            expect(substr($str, 75, 1))->toBe(' ');
            // Publisher Type (2 L)
            expect(substr($str, 76, 2))->toBe(str_pad('E', 2, ' '));
            // Tax ID # (9 A defaults blank)
            expect(substr($str, 78, 9))->toBe(str_repeat(' ', 9));
            // Publisher IPI Name # (11 L)
            expect(substr($str, 87, 11))->toBe(str_pad('', 11, ' '));
            // Submitter Agreement Number (14 A)
            expect(substr($str, 98, 14))->toBe(str_repeat(' ', 14));
            // PR Affiliation Society # (3 L)
            expect(substr($str, 112, 3))->toBe('000');
            // PR Ownership Share (5 N)
            expect(substr($str, 115, 5))->toBe('00000');
            // MR Affiliation Society # (3 L)
            expect(substr($str, 120, 3))->toBe('000');
            // MR Ownership Share (5 N)
            expect(substr($str, 123, 5))->toBe('00000');
            // SR Affiliation Society # (3 L)
            expect(substr($str, 128, 3))->toBe('000');
            // SR Ownership Share (5 N)
            expect(substr($str, 131, 5))->toBe('00000');
            // Special Agreements Indicator (1)
            expect(substr($str, 136, 1))->toBe(' ');
            // First Recording Refusal Indicator (1)
            expect(substr($str, 137, 1))->toBe(' ');
            // Filler (1)
            expect(substr($str, 138, 1))->toBe(' ');
        });
    });

    it('throws when setRecordPrefix is not called', function () {
        $record = new SpuRecord(1,'IP00001','MyPub','E');

        $record->toString();
    })->throws(\LogicException::class, 'The record prefix for LabelTools\PhpCwrExporter\Records\SpuRecord has not been set.');

    describe('Field-level validation', function () {
        it('throws when Publisher Sequence # is less than 1', function () {
            new SpuRecord(0, 'IP', 'Pub', 'E', '', 'IPI');
        })->throws(InvalidArgumentException::class);

        it('throws when Interested Party # is empty', function () {
            (new SpuRecord(1, '', 'Pub', 'E', '', 'IPI'))->toString();
        })->throws(InvalidArgumentException::class);

        it('throws when Publisher Name is empty', function () {
            new SpuRecord(1, 'IP', '', 'E', '', 'IPI');
        })->throws(InvalidArgumentException::class);

        it('throws when Publisher Type is invalid', function () {
            (new SpuRecord(1, 'IP', 'Pub', 'XX', '', 'IPI'))->toString();
        })->throws(InvalidArgumentException::class);

        it('throws when Tax ID is non-numeric', function () {
            (new SpuRecord(1, 'IP', 'Pub', 'E', 'ABC', 'IPI'))->toString();
        })->throws(InvalidArgumentException::class);

        // it('throws when Publisher IPI Name # is empty', function () {
        //     new SpuRecord(1, 'IP', 'Pub', 'E', '', '');
        // })->throws(InvalidArgumentException::class);

        it('throws when PR Ownership Share is out of range', function () {
            (new SpuRecord(1, 'IP', 'Pub', 'E', '', 'IPI'))
                ->setPrOwnershipShare(5001);
        })->throws(InvalidArgumentException::class);

        it('throws when MR Ownership Share is out of range', function () {
            (new SpuRecord(1, 'IP', 'Pub', 'E', '', 'IPI'))
                ->setMrOwnershipShare(10001);
        })->throws(InvalidArgumentException::class);

        it('throws when SR Ownership Share is out of range', function () {
            (new SpuRecord(1, 'IP', 'Pub', 'E', '', 'IPI'))
                ->setSrOwnershipShare(10001);
        })->throws(InvalidArgumentException::class);

        it('throws when PR Affiliation Society is invalid', function () {
            (new SpuRecord(1, 'IP', 'Pub', 'E', '', 'IPI'))
                ->setPrAffiliationSociety('999');
        })->throws(InvalidArgumentException::class);

        it('throws when MR Affiliation Society is invalid', function () {
            (new SpuRecord(1, 'IP', 'Pub', 'E', '', 'IPI'))
                ->setMrSociety('999');
        })->throws(InvalidArgumentException::class);

        it('throws when SR Affiliation Society is invalid', function () {
            (new SpuRecord(1, 'IP', 'Pub', 'E', '', 'IPI'))
                ->setSrSociety('999');
        })->throws(InvalidArgumentException::class);
    });
});