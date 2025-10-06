<?php

use LabelTools\PhpCwrExporter\Records\PwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\PwrRecord as V21PwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\PwrRecord as V22PwrRecord;

describe('PWR (Publisher For Writer) Record', function () {
    describe('Base', function () {
        it('builds a minimal valid PWR record with correct padding & format', function () {

            $record = new PwrRecord(
                publisherIpNumber: 'PUBLISHER',
                publisherName: 'Acme Publishing'
            );

            $out = $record->setRecordPrefix(0, 0)->toString();


            expect(strlen($out))->toBe(19 + 9 + 45 + 14 + 14);

            // 0-2:   "PWR"
            expect(substr($out, 0, 3))->toBe('PWR');

            // 3-18: Transaction Sequence (8) and Record Sequence (8), both zero-padded
            expect(substr($out, 3, 16))->toBe(str_repeat('0', 16));

            // 19-27: Publisher IP # (9 chars). We passed "PUBLISHER" (8 chars) → "PUBLISHER" + one trailing space
            expect(substr($out, 19, 9))->toBePadded('PUBLISHER', 9);

            // 28-72: Publisher Name (45 chars). We passed "Acme Publishing" (14 chars):
            expect(substr($out, 28, 45))->toBePadded('Acme Publishing', 45);

            // 73-86: Submitter Agreement Number (14 chars) → all spaces
            expect(substr($out, 73, 14))->toBePadded('', 14);

            // 87-100: Society-Assigned Agreement Number (14 chars) → all spaces
            expect(substr($out, 87, 14))->toBePadded('', 14);
        });

        it('throws when setRecordPrefix is not called', function () {
            $record = new PwrRecord(
                publisherIpNumber: 'PUBLISHER',
                publisherName: 'Acme Publishing'
            );

            $record->toString();
        })->throws(\LogicException::class, 'The record prefix for LabelTools\PhpCwrExporter\Records\PwrRecord has not been set.');

        it('throws when Publisher IP # is empty', function () {
            new PwrRecord(
                publisherIpNumber: '',
                publisherName: 'Valid Name'
            );
        })->throws(\InvalidArgumentException::class, 'Publisher IP # must be 1-9 characters.');

        it('throws when Publisher IP # is longer than 9 characters', function () {
            new PwrRecord(
                publisherIpNumber: 'TOO_LONG_IP_ADDRESS', // 17 chars
                publisherName: 'Valid Name'
            );
        })->throws(\InvalidArgumentException::class, 'Publisher IP # must be 1-9 characters.');

        it('throws when Publisher Name is empty', function () {
            new PwrRecord(
                publisherIpNumber: 'VALIDIP',
                publisherName: ''
            );
        })->throws(\InvalidArgumentException::class, 'Publisher Name is required and must not exceed 45 characters.');

        it('throws when Publisher Name exceeds 45 characters', function () {
            $longName = str_repeat('A', 46);
            new PwrRecord(
                publisherIpNumber: 'VALIDIP',
                publisherName: $longName
            );
        })->throws(\InvalidArgumentException::class, 'Publisher Name is required and must not exceed 45 characters.');

        it('throws when Submitter Agreement Number is longer than 14 characters', function () {
            new PwrRecord(
                publisherIpNumber: 'VALIDIP',
                publisherName: 'Valid Publisher',
                submitterAgreementNumber: str_repeat('X', 15) // 15 chars
            );
        })->throws(\InvalidArgumentException::class, 'Submitter Agreement Number must not exceed 14 characters.');

        it('throws when Society-Assigned Agreement Number is longer than 14 characters', function () {
            new PwrRecord(
                publisherIpNumber: 'VALIDIP',
                publisherName: 'Valid Publisher',
                submitterAgreementNumber:  '',
                societyAssignedAgreementNumber: str_repeat('Y', 15)
            );
        })->throws(\InvalidArgumentException::class, 'Society-Assigned Agreement Number must not exceed 14 characters.');

        it('accepts valid optional 2.0 fields and embeds them correctly', function () {
            $record = new PwrRecord(
                publisherIpNumber: 'PUBIP123',
                publisherName: 'Example Pub',
                submitterAgreementNumber: 'AGR1234567890',
                societyAssignedAgreementNumber: 'SOC9876543210'
            );

            $out = $record->setRecordPrefix(0, 0)->toString();

            // Check total length
            expect(strlen($out))->toBe(101);

            // Check Submitter Agreement (#) at bytes 74-87 (0-based offset 73)
            $submittedField = substr($out, 73, 14);
            expect($submittedField)->toBePadded('AGR1234567890', 14);

            // Check Society-Assigned Agreement (#) at bytes 88-101 (offset 87)
            $societyField = substr($out, 87, 14);
            expect($societyField)->toBePadded('SOC9876543210', 14);
        });
    });

    describe('CWR v2.1', function () {
        it('builds a valid PWR record ', function () {
            $swr = new V21PwrRecord(
                publisherIpNumber: 'PUBIP123',
                publisherName: 'Example Pub',
                submitterAgreementNumber: 'AGR1234567890',
                societyAssignedAgreementNumber: 'SOC9876543210',
                writerIpNumber: 'LINK001'
            );
            $record = $swr->setRecordPrefix(0,0)->toString();

            expect(strlen($record))->toBe(19 + 9 + 45 + 14 + 14 + 9);
            expect(substr($record, 101, 9))->toBePadded('LINK001', 9);
        });
    });

    describe('CWR v2.2', function () {
        it('builds a valid PWR record ', function () {
            $swr = new V22PwrRecord(
                publisherIpNumber: 'PUBIP123',
                publisherName: 'Example Pub',
                submitterAgreementNumber: 'AGR1234567890',
                societyAssignedAgreementNumber: 'SOC9876543210',
                writerIpNumber: 'LINK001',
                publisherSequenceNumber: 1

            );
            $record = $swr->setRecordPrefix(0, 0)->toString();

            expect(strlen($record))->toBe(19 + 9 + 45 + 14 + 14 + 9 + 2);
            expect(substr($record, 110, 2))->toBe('01');
        });
    });

});