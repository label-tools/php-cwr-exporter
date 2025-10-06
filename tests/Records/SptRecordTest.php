<?php

use LabelTools\PhpCwrExporter\Records\SptRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\SptRecord as V21SptRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SptRecord as V22SptRecord;

describe('SPT (Publisher Territory) Record', function () {
    it('builds a minimal valid SPT record with correct padding & format', function () {
        $record = new SptRecord(
            interestedPartyNumber: 'ABCDEFGHI',
            prCollectionShare: 1234,
            mrCollectionShare: 5000,
            srCollectionShare: 10000,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: ''
        );

        $out = $record->setRecordPrefix(0, 0)->toString();

        // TOTAL LENGTH = 19 + 9 + 6 + 5 + 5 + 5 + 1 + 4 + 1 = 55
        expect(strlen($out))->toBe(55);

        // 0–18: “SPT” + zero-padded prefix
        expect(substr($out, 0, 3))->toBe('SPT');
        expect(substr($out, 3, 16))->toBe(str_repeat('0', 16));

        // 19–27: Interested Party # (9 chars)
        expect(substr($out, 19, 9))->toBe('ABCDEFGHI');

        // 28–33: Constant 6 spaces
        expect(substr($out, 28, 6))->toBe(str_repeat(' ', 6));

        // 34–38: PR collection share (“01234”)
        expect(substr($out, 34, 5))->toBe('01234');

        // 39–43: MR collection share (“05000”)
        expect(substr($out, 39, 5))->toBe('05000');

        // 44–48: SR collection share (“10000”)
        expect(substr($out, 44, 5))->toBe('10000');

        // 49: Inclusion/Exclusion (“I”)
        expect(substr($out, 49, 1))->toBe('I');

        // 50–53: TIS code (4 chars, left-aligned). We passed 840 → “840 ”
        expect(substr($out, 50, 4))->toBe('840 ');

        // 54: Shares Change (blank)
        expect(substr($out, 54, 1))->toBe(' ');
    });

    it('throws when setRecordPrefix is not called', function () {
        $record = new SptRecord(
            interestedPartyNumber: 'ABCDEFGHI',
            prCollectionShare: 1234,
            mrCollectionShare: 5000,
            srCollectionShare: 10000,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: ''
        );
        $record->toString();
    })->throws(\LogicException::class, 'The record prefix for LabelTools\PhpCwrExporter\Records\SptRecord has not been set.');

    it('throws when Interested Party Number is empty', function () {
        new SptRecord(
            interestedPartyNumber: '',
            prCollectionShare: 0,
            mrCollectionShare: 0,
            srCollectionShare: 0,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: ''
        );
    })->throws(InvalidArgumentException::class, 'Interested Party Number must be non-empty and at most 9 characters.');

    it('throws when Interested Party Number is longer than 9 characters', function () {
        new SptRecord(
            interestedPartyNumber: 'TOO_LONG_ID',    // 10 chars
            prCollectionShare: 0,
            mrCollectionShare: 0,
            srCollectionShare: 0,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: ''
        );
    })->throws(InvalidArgumentException::class, 'Interested Party Number must be non-empty and at most 9 characters.');

    it('throws when PR Collection Share is negative', function () {
       expect(fn() => new SptRecord(
            interestedPartyNumber: 'PARTY123',
            prCollectionShare: -1,
            mrCollectionShare: 0,
            srCollectionShare: 0,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: ''
        ))->toThrow(InvalidArgumentException::class)
            ->and(fn($e) => str_contains($e->getMessage(), 'must be between 0 and 5000'));
    });

    it('throws when PR Collection Share exceeds 5000 (50.00%)', function () {
        expect(fn() => new SptRecord(
            interestedPartyNumber: 'PARTY123',
            prCollectionShare: 5001,
            mrCollectionShare: 0,
            srCollectionShare: 0,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: ''
        ))->toThrow(InvalidArgumentException::class)
            ->and(fn($e) => str_contains($e->getMessage(), 'must be between 0 and 5000'));
    });

    it('throws when MR Collection Share is negative', function () {
        expect(fn() => new SptRecord(
            interestedPartyNumber: 'PARTY123',
            prCollectionShare: 0,
            mrCollectionShare: -1,
            srCollectionShare: 0,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: ''
        ))->toThrow(InvalidArgumentException::class)
            ->and(fn($e) => str_contains($e->getMessage(), 'must be between 0 and 10000'));
    });

    it('throws when MR Collection Share exceeds 10000 (100.00%)', function () {
        expect(fn() => new SptRecord(
            interestedPartyNumber: 'PARTY123',
            prCollectionShare: 0,
            mrCollectionShare: 10001,
            srCollectionShare: 0,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: ''
        ))->toThrow(InvalidArgumentException::class)
            ->and(fn($e) => str_contains($e->getMessage(), 'must be between 0 and 10000'));
    });

    it('throws when SR Collection Share is negative', function () {
        expect(fn() => new SptRecord(
            interestedPartyNumber: 'PARTY123',
            prCollectionShare: 0,
            mrCollectionShare: 0,
            srCollectionShare: -1,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: ''
        ))->toThrow(InvalidArgumentException::class)
            ->and(fn($e) => str_contains($e->getMessage(), 'must be between 0 and 10000'));
    });

    it('throws when SR Collection Share exceeds 10000 (100.00%)', function () {
        expect(fn() => new SptRecord(
            interestedPartyNumber: 'PARTY123',
            prCollectionShare: 0,
            mrCollectionShare: 0,
            srCollectionShare: 10001,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: ''
        ))->toThrow(InvalidArgumentException::class)
            ->and(fn($e) => str_contains($e->getMessage(), 'must be between 0 and 10000'));
    });

    it('throws when Inclusion/Exclusion Indicator is invalid', function () {
        new SptRecord(
            interestedPartyNumber: 'PARTY123',
            prCollectionShare: 0,
            mrCollectionShare: 0,
            srCollectionShare: 0,
            inclusionExclusionIndicator: 'X',
            tisNumericCode: 840,
            sharesChange: ''
        );
    })->throws(InvalidArgumentException::class, 'Inclusion/Exclusion Indicator must be "I" or "E".');

    it('throws when TIS Numeric Code is not in TisCode enum', function () {
        new SptRecord(
            interestedPartyNumber: 'PARTY123',
            prCollectionShare: 0,
            mrCollectionShare: 0,
            srCollectionShare: 0,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 9999,
            sharesChange: ''
        );
    })->throws(InvalidArgumentException::class, 'Invalid TIS Numeric Code.');

    it('throws when Shares Change flag is not "" or "Y"', function () {
        new SptRecord(
            interestedPartyNumber: 'PARTY123',
            prCollectionShare: 0,
            mrCollectionShare: 0,
            srCollectionShare: 0,
            inclusionExclusionIndicator: 'I',
            tisNumericCode: 840,
            sharesChange: 'Z'
        );
    })->throws(InvalidArgumentException::class, 'Shares Change flag must be empty or "Y".');

    it('accepts “E” for Inclusion/Exclusion (excluded) and “Y” for Shares Change', function () {
        $record = new SptRecord(
            interestedPartyNumber: 'PUBLISHER',
            prCollectionShare: 2500,
            mrCollectionShare: 7500,
            srCollectionShare: 1000,
            inclusionExclusionIndicator: 'E',
            tisNumericCode: '250',
            sharesChange: 'Y'
        );

        $out = $record->setRecordPrefix(0, 0)->toString();

        // Check Inclusion/Exclusion = “E” at position 49
        expect(substr($out, 49, 1))->toBe('E');

        // Check TIS (4 chars) = "250 "
        expect(substr($out, 50, 4))->toBe('250 ');

        // Check Shares Change = “Y”
        expect(substr($out, 54, 1))->toBe('Y');

        // Check numeric formatting:
        expect(substr($out, 34, 5))->toBe('02500'); // PR
        expect(substr($out, 39, 5))->toBe('07500'); // MR
        expect(substr($out, 44, 5))->toBe('01000'); // SR
    });

    describe('CWR v2.1', function () {
       it('builds a valid SPT record', function () {

            $record = new V21SptRecord(
                interestedPartyNumber: 'PARTY1',
                prCollectionShare: 3000,
                mrCollectionShare: 4000,
                srCollectionShare: 5000,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 344,
            );

            $record->setSequenceNumber(7);

            $out21 = $record->setRecordPrefix(0, 0)->toString();

            // TOTAL LENGTH (v2.1) = 55 + 3 = 58
            expect(strlen($out21))->toBe(58);

            // 0–53: inspect exactly as before up through TIS & sharesChange
            expect(substr($out21, 0, 3))->toBe('SPT');
            expect(substr($out21, 3, 16))->toBe(str_repeat('0', 16));
            expect(substr($out21, 19, 9))->toBe('PARTY1   ');
            expect(substr($out21, 28, 6))->toBe(str_repeat(' ', 6));
            expect(substr($out21, 34, 5))->toBe('03000');
            expect(substr($out21, 39, 5))->toBe('04000');
            expect(substr($out21, 44, 5))->toBe('05000');
            expect(substr($out21, 49, 1))->toBe('I');
            expect(substr($out21, 50, 4))->toBe('344 ');
            expect(substr($out21, 54, 1))->toBe(' ');
            expect(substr($out21, 55, 3))->toBe('007');
        });
    });

    describe('CWR v2.2', function () {
        it('builds a valid SPT record', function () {

            $record = new V22SptRecord(
                interestedPartyNumber: 'PARTY1',
                prCollectionShare: 3000,
                mrCollectionShare: 4000,
                srCollectionShare: 5000,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 344,
            );

            $record->setRecordPrefix(0, 0)->setSequenceNumber(7);
            expect(strlen($record->toString()))->toBe(58);
        });
    });


});