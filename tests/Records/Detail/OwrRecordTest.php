<?php

use LabelTools\PhpCwrExporter\Enums\SocietyCode;
use LabelTools\PhpCwrExporter\Enums\WriterDesignation;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\OwrRecord;

describe('OWR (Other Writer) Record', function () {
    it('builds an OWR record with the same layout as SWR', function () {
        $record = (new OwrRecord(
            interestedPartyNumber: 'OWR123456',
            writerLastName: 'Smith',
            writerFirstName: 'Ada',
            writerDesignationCode: WriterDesignation::COMPOSER,
            taxId: 'ABC123',
            writerIpiNameNumber: '12345678901',
            prAffiliationSociety: SocietyCode::ASCAP,
            prOwnershipShare: 20,
            mrAffiliationSociety: 8,
            mrOwnershipShare: 30,
            srAffiliationSociety: SocietyCode::PRS,
            srOwnershipShare: 40,
            reversionaryIndicator: 'Y',
            firstRecordingRefusalIndicator: 'N',
            workForHireIndicator: 'Y',
            writerIpiBaseNumber: 'ABCDEFGHIJKLM',
            personalNumber: '123456789012',
            usaLicenseIndicator: 'Y'
        ))->setRecordPrefix(0, 1)->toString();

        // SWR base length (179) + USA License Indicator (1)
        expect(strlen($record))->toBe(180);
        expect(substr($record, 0, 3))->toBe('OWR');
        expect(substr($record, 19, 9))->toBe('OWR123456');
        expect(substr($record, 28, 5))->toBe('Smith');
        expect(substr($record, 73, 3))->toBe('Ada');
        expect(substr($record, 103, 1))->toBe(' ');
        expect(substr($record, 104, 2))->toBePadded(WriterDesignation::COMPOSER->value, 2);
        expect(substr($record, 126, 3))->toBePaddedLeft(SocietyCode::ASCAP->value, 3, '0');
        expect(substr($record, 129, 5))->toBe('02000');
        expect(substr($record, 179, 1))->toBe('Y'); // USA License Indicator from v2.1 extension
    });

    it('requires a last name', function () {
        (new OwrRecord(
            interestedPartyNumber: 'OWR1',
            writerLastName: '',
            writerDesignationCode: WriterDesignation::COMPOSER
        ))->setRecordPrefix(0, 3)->toString();
    })->throws(\InvalidArgumentException::class, 'Last Name is required and max 45 chars.');
});
