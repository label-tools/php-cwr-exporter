<?php

use LabelTools\PhpCwrExporter\Records\SwtRecord;
use LabelTools\PhpCwrExporter\Enums\TisCode;
use LabelTools\PhpCwrExporter\Version\V21\Records\SwtRecord as V21SwtRecord;

describe('SWT (Song Territory) Record', function () {
    describe('Base', function () {
        it('builds a minimal valid SWT record with correct padding & format', function () {
            //   - Interested Party #: “PUBLISHER” (9 chars)
            //   - PR share = 00000
            //   - MR share = 00000
            //   - SR share = 00000
            //   - Inclusion/Exclusion = “I”
            //   - TIS code = 840 (USA) → left-aligned in 4 chars: “840 ”
            //   - Shares Change = blank
            $record = new SwtRecord(
                interestedPartyNumber: 'PARTY1',
                prCollectionShare: 0,
                mrCollectionShare: 0,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: TisCode::AUSTRALIA,
                sharesChange: ''
            );

            $out = $record->toString();

            expect(strlen($out))->toBe(19 + 9 + 5 + 5 + 5 + 1 + 4 + 1);
            expect(substr($out, 0, 19))->toBePadded('SWT', 19);
            expect(substr($out, 19, 9))->toBePadded('PARTY1', 9);
            expect(substr($out, 28, 5))->toBe('00000');
            expect(substr($out, 33, 5))->toBe('00000');
            expect(substr($out, 38, 5))->toBe('00000');
            expect(substr($out, 43, 1))->toBe('I');
            expect(substr($out, 44, 4))->toBePadded(TisCode::AUSTRALIA->value, 4);
            expect(substr($out, 48, 1))->toBe(' ');
        });

        it('throws when Interested Party Number is empty', function () {
            new SwtRecord(
                interestedPartyNumber: '',
                prCollectionShare: 0,
                mrCollectionShare: 0,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 840,
                sharesChange: '',
            );
        })->throws(InvalidArgumentException::class, 'Interested Party Number must be 1–9 characters.');

        it('throws when Interested Party Number is longer than 9 characters', function () {
            new SwtRecord(
                interestedPartyNumber: 'TOO_LONG_ID', // 10 chars
                prCollectionShare: 0,
                mrCollectionShare: 0,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 840,
                sharesChange: '',
            );
        })->throws(InvalidArgumentException::class, 'Interested Party Number must be 1–9 characters.');

        it('throws when PR Collection Share is negative', function () {
            new SwtRecord(
                interestedPartyNumber: 'PARTY123',
                prCollectionShare:           -1,
                mrCollectionShare: 0,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 840,
                sharesChange: '',
            );
        })->throws(InvalidArgumentException::class, 'PR Collection Share must be between 0 and 10000.');

        it('throws when PR Collection Share exceeds 10000', function () {
            new SwtRecord(
                interestedPartyNumber: 'PARTY123',
                prCollectionShare: 10001,
                mrCollectionShare: 0,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 840,
                sharesChange: '',
            );
        })->throws(InvalidArgumentException::class, 'PR Collection Share must be between 0 and 10000.');

        it('throws when MR Collection Share is negative', function () {
            new SwtRecord(
                interestedPartyNumber: 'PARTY123',
                prCollectionShare: 0,
                mrCollectionShare:           -1,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 840,
                sharesChange: '',
            );
        })->throws(InvalidArgumentException::class, 'MR Collection Share must be between 0 and 10000.');

        it('throws when MR Collection Share exceeds 10000', function () {
            new SwtRecord(
                interestedPartyNumber: 'PARTY123',
                prCollectionShare: 0,
                mrCollectionShare:           10001,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 840,
                sharesChange: '',
            );
        })->throws(InvalidArgumentException::class, 'MR Collection Share must be between 0 and 10000.');

        it('throws when SR Collection Share is negative', function () {
            new SwtRecord(
                interestedPartyNumber: 'PARTY123',
                prCollectionShare: 0,
                mrCollectionShare: 0,
                srCollectionShare:           -1,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 840,
                sharesChange: '',
            );
        })->throws(InvalidArgumentException::class, 'SR Collection Share must be between 0 and 10000.');

        it('throws when SR Collection Share exceeds 10000', function () {
            new SwtRecord(
                interestedPartyNumber: 'PARTY123',
                prCollectionShare: 0,
                mrCollectionShare: 0,
                srCollectionShare:           10001,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 840,
                sharesChange: '',
            );
        })->throws(InvalidArgumentException::class, 'SR Collection Share must be between 0 and 10000.');

        it('throws when Inclusion/Exclusion Indicator is invalid', function () {
            new SwtRecord(
                interestedPartyNumber: 'PARTY123',
                prCollectionShare: 0,
                mrCollectionShare: 0,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'X',
                tisNumericCode: 840,
                sharesChange: '',
            );
        })->throws(InvalidArgumentException::class, 'Inclusion/Exclusion Indicator must be \'I\' or \'E\'.');

        it('throws when TIS Numeric Code is not in TisCode enum', function () {
            new SwtRecord(
                interestedPartyNumber: 'PARTY123',
                prCollectionShare: 0,
                mrCollectionShare: 0,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'I',
                tisNumericCode:              9999, // not valid
                sharesChange: '',
            );
        })->throws(InvalidArgumentException::class, 'Invalid TIS code: 9999');

        it('throws when Shares Change is invalid', function () {
            new SwtRecord(
                interestedPartyNumber: 'PARTY123',
                prCollectionShare: 0,
                mrCollectionShare: 0,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: 840,
                sharesChange: 'Z', // invalid
            );
        })->throws(InvalidArgumentException::class, 'Shares Change flag must be blank or \'Y\'.');

        // it('throws when Sequence Number is out of range (<1)', function () {
        //     new SwtRecord(
        //         interestedPartyNumber: 'PARTY123',
        //         prCollectionShare: 0,
        //         mrCollectionShare: 0,
        //         srCollectionShare: 0,
        //         inclusionExclusionIndicator: 'I',
        //         tisNumericCode: 840,
        //         sharesChange: '',
        //     );
        // })->throws(InvalidArgumentException::class, 'Sequence Number must be between 1 and 999.');

        // it('throws when Sequence Number is out of range (>999)', function () {
        //     new SwtRecord(
        //         interestedPartyNumber: 'PARTY123',
        //         prCollectionShare: 0,
        //         mrCollectionShare: 0,
        //         srCollectionShare: 0,
        //         inclusionExclusionIndicator: 'I',
        //         tisNumericCode: 840,
        //         sharesChange: '',000
        //     );
        // })->throws(InvalidArgumentException::class, 'Sequence Number must be between 1 and 999.');

        it('accepts valid flags and sharesChange "Y"', function () {
            $record = new SwtRecord(
                interestedPartyNumber: 'PUBLISHER',
                prCollectionShare:           5000,    // 05000
                mrCollectionShare:           7500,    // 07500
                srCollectionShare:           2500,    // 02500
                inclusionExclusionIndicator: 'E',
                tisNumericCode:              TisCode::UNITED_STATES,
                sharesChange: 'Y'
            );

            $out = $record->toString();

            // Check Inclusion/Exclusion = "E" at position 43
            expect(substr($out, 43, 1))->toBe('E');

            // Check TIS Numeric Code (4 chars) = "840 " (USA)
            expect(substr($out, 44, 4))->toBe('840 ');

            // Check Shares Change = "Y"
            expect(substr($out, 48, 1))->toBe('Y');

            // Check numeric formatting:
            expect(substr($out, 28, 5))->toBe('05000'); // PR
            expect(substr($out, 33, 5))->toBe('07500'); // MR
            expect(substr($out, 38, 5))->toBe('02500'); // SR
        });
    });
    describe('CWR v2.1', function () {
        it('builds a valid v2.1 SWT record ', function () {
            $swr = new V21SwtRecord(
                 interestedPartyNumber: 'PARTY1',
                prCollectionShare: 0,
                mrCollectionShare: 0,
                srCollectionShare: 0,
                inclusionExclusionIndicator: 'I',
                tisNumericCode: TisCode::AUSTRALIA,
                sharesChange: '',
                sequenceNum: 1
            );
            $record = $swr->toString();

            expect(strlen($record))->toBe(49+3);
            expect(substr($record, 49, 3))->toBe('001');

        });


    });
});