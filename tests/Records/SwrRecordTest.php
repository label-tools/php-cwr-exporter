

<?php

use LabelTools\PhpCwrExporter\Enums\SocietyCode;
use LabelTools\PhpCwrExporter\Enums\WriterDesignation;
use LabelTools\PhpCwrExporter\Records\SwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\SwrRecord as V21SwrRecord;

describe('SWR (Writer) Record', function () {
    describe('Main', function () {
        it('builds a minimal valid SWR record according to the spec', function () {
            $swr = new SwrRecord(
                interestedPartyNumber: 'WRTR12345', // 9 chars
                writerLastName: 'Doe',
                writerFirstName: 'John',
                writerDesignationCode: WriterDesignation::COMPOSER,
                taxId: '123456789',
                writerIpiNameNumber: 'I2345678901',
                prAffiliationSociety: SocietyCode::ASCAP,
                prOwnershipShare: 1000,
                mrAffiliationSociety: SocietyCode::BMI,
                mrOwnershipShare: 5000,
                srAffiliationSociety: SocietyCode::PRS,
                srOwnershipShare: 2500
            );
            $record = $swr->toString();

            // total length = 19+9+45+30+1+2+9+11+3+5+3+5+3+5+1+1+1+1+13+12 = 179
            expect(strlen($record))->toBe(179);

            // 0-2: "SWR"
            expect(substr($record, 0, 3))->toBe('SWR');
            // 3-18: 16 spaces
            expect(substr($record, 3, 16))->toBe(str_repeat(' ', 16));

            // 19-27: Interested Party #
            expect(substr($record, 19, 9))->toBe('WRTR12345');

            // 28-72: Last Name left-aligned, padded to 45
            expect(substr($record, 28, 3))->toBe('Doe');
            expect(substr($record, 31, 42))->toBe(str_repeat(' ', 42));

            // 73-102: First Name left-aligned, padded to 30
            expect(substr($record, 73, 4))->toBe('John');
            expect(substr($record, 77, 26))->toBe(str_repeat(' ', 26));

            // 103: Writer Unknown Indicator (blank)
            expect(substr($record, 103, 1))->toBe(' ');

            // 104-105: Writer Designation Code
            expect(substr($record, 104, 2))->toBePadded(WriterDesignation::COMPOSER->value, 2);

            // 106-114: Tax ID
            expect(substr($record, 106, 9))->toBe('123456789');

            // 115-125: IPI Name Number
            expect(substr($record, 115, 11))->toBe('I2345678901');

            // 126-128: PR Affiliation Society
            expect(substr($record, 126, 3))->toBePaddedLeft(SocietyCode::ASCAP->value, 3, '0');

            // 129-133: PR Ownership Share “01000”
            expect(substr($record, 129, 5))->toBe('01000');

            // 134-136: MR Affiliation Society
            expect(substr($record, 134, 3))->toBePaddedLeft(SocietyCode::BMI->value, 3, '0');

            // 137-141: MR Ownership Share “05000”
            expect(substr($record, 137, 5))->toBe('05000');

            // 142-144: SR Affiliation Society
            expect(substr($record, 142, 3))->toBePaddedLeft(SocietyCode::PRS->value, 3, '0');

            // 145-149: SR Ownership Share “02500”
            expect(substr($record, 145, 5))->toBe('02500');

            // 150: Reversionary (blank)
            expect(substr($record, 150, 1))->toBe(' ');

            // 151: First Recording Refusal (blank)
            expect(substr($record, 151, 1))->toBe(' ');

            // 152: Work For Hire (blank)
            expect(substr($record, 152, 1))->toBe(' ');

            // 153: Filler (space)
            expect(substr($record, 153, 1))->toBe(' ');

            // 154-166: IPI Base Number (blank)
            expect(substr($record, 154, 13))->toBe(str_repeat(' ', 13));

            // 167-178: Personal Number (blank)
            expect(substr($record, 167, 12))->toBe(str_repeat(' ', 12));
        });

        it('throws when Interested Party Number is empty', function () {
            new SwrRecord(
                interestedPartyNumber: '',
                writerLastName: 'Doe',
                writerFirstName: '',
                writerDesignationCode: WriterDesignation::COMPOSER,
                taxId: '',
                writerIpiNameNumber: '',
            );
        })->throws(InvalidArgumentException::class, 'Interested Party Number must ');

        it('throws when Last Name is empty', function () {
            new SwrRecord(
                interestedPartyNumber: 'WRTR12345',
                writerLastName: '',
                writerFirstName: '',
                writerDesignationCode: WriterDesignation::COMPOSER,
            );
        })->throws(InvalidArgumentException::class, 'Last Name is required and max 45 chars');

        it('throws when Writer Designation Code is invalid', function () {
            new SwrRecord(
                interestedPartyNumber: 'WRTR12345',
                writerLastName: 'Doe',
                writerFirstName: '',
                writerDesignationCode: 'A!',
            );
        })->throws(InvalidArgumentException::class, 'Invalid Writer Designation Code: A!');

        it('throws when PR Ownership Share out of range', function () {
            new SwrRecord(
                interestedPartyNumber: 'WRTR12345',
                writerLastName: 'Doe',
                writerFirstName: 'John',
                writerDesignationCode: WriterDesignation::COMPOSER,
                prAffiliationSociety:  SocietyCode::ASCAP,
                prOwnershipShare: 100001, // >10000 (100.00)

            );
        })->throws(InvalidArgumentException::class, 'PR Ownership Share must be between 0 and 10000.');

        it('throws when MR Ownership Share out of range', function () {
            new SwrRecord(
                interestedPartyNumber: 'WRTR12345',
                writerLastName: 'Doe',
                writerFirstName: 'John',
                writerDesignationCode: WriterDesignation::COMPOSER,
                mrAffiliationSociety: SocietyCode::BMI,
                mrOwnershipShare: -1,
            );
        })->throws(InvalidArgumentException::class, 'MR Ownership Share must be between 0 and 10000.');

        it('throws when SR Ownership Share out of range', function () {
            new SwrRecord(
                interestedPartyNumber: 'WRTR12345',
                writerLastName: 'Doe',
                writerFirstName: 'John',
                writerDesignationCode: WriterDesignation::COMPOSER,
                srAffiliationSociety: SocietyCode::PRS,
                srOwnershipShare: 100001
            );
        })->throws(InvalidArgumentException::class, 'SR Ownership Share must be between 0 and 10000.');
    });

    describe('CWR v2.1', function () {
        it('builds a valid v2.1 SWR record including USA License Indicator', function () {
            $swr = new V21SwrRecord(
                interestedPartyNumber: 'WRITER001',
                writerLastName: 'Smith',
                writerFirstName: 'Jane',
                writerDesignationCode: 'CA',
                taxId: '987654321',
                writerIpiNameNumber: 'I1234567890',
                prAffiliationSociety: SocietyCode::BMI,
                prOwnershipShare: 2000,
                mrAffiliationSociety: 8,
                mrOwnershipShare: 3000,
                srAffiliationSociety: SocietyCode::PRS,
                srOwnershipShare: 4000,
                reversionaryIndicator: 'Y',
                firstRecordingRefusalIndicator: 'N',
                workForHireIndicator: 'Y',
                filler: '',
                writerIpiBaseNumber: 'ABCDEFGHIJKLM',
                personalNumber: '123456789012',
                usaLicenseIndicator: 'Y'
            );
            $record = $swr->toString();

            // Length = base (179) + 1 = 180
            expect(strlen($record))->toBe(180);

            // Check USA License Indicator at last position (offset 179, length 1)
            expect(substr($record, 179, 1))->toBe('Y');
        });

        it('throws when USA License Indicator is invalid', function () {
            new V21SwrRecord(
                interestedPartyNumber: 'WRITER002',
                writerLastName: 'Smith',
                writerFirstName: 'Jane',
                writerDesignationCode: 'A',
                usaLicenseIndicator: 'X'
            );
        })->throws(InvalidArgumentException::class, "USA License Indicator must be 'Y' or blank.");
    });
});