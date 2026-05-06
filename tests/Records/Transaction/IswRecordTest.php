<?php

use LabelTools\PhpCwrExporter\Records\Transaction\IswRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\IswRecord as V21IswRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\Transaction\IswRecord as V22IswRecord;

describe('Notification of ISWC (ISW) Record', function () {
    it('builds the base ISW record with the NWR work-header layout', function () {
        $record = (new IswRecord(
            workTitle: 'Assigned Work',
            submitterWorkNumber: 'WORK123',
            mwDistributionCategory: 'POP',
            versionType: 'ORI',
            iswc: 'T3402983517',
        ))->setRecordPrefix(0, 0)->toString();

        expect(strlen($record))->toBe(259)
            ->and(substr($record, 0, 3))->toBe('ISW')
            ->and(substr($record, 19, 60))->toBePadded('ASSIGNED WORK', 60)
            ->and(substr($record, 81, 14))->toBePadded('WORK123', 14)
            ->and(substr($record, 95, 11))->toBe('T3402983517');
    });

    it('builds versioned ISW records', function () {
        $v21 = (new V21IswRecord(
            workTitle: 'Assigned Work',
            submitterWorkNumber: 'WORK123',
            mwDistributionCategory: 'POP',
            versionType: 'ORI',
            iswc: 'T3402983517',
        ))->setRecordPrefix(0, 0)->toString();

        $v22 = (new V22IswRecord(
            workTitle: 'Assigned Work',
            submitterWorkNumber: 'WORK123',
            mwDistributionCategory: 'POP',
            versionType: 'ORI',
            iswc: 'T3402983517',
        ))->setRecordPrefix(0, 0)->toString();

        expect(strlen($v21))->toBe(260)
            ->and(strlen($v22))->toBe(260)
            ->and(substr($v21, 0, 3))->toBe('ISW')
            ->and(substr($v22, 0, 3))->toBe('ISW');
    });

    it('parses the ISW work fields', function () {
        $line = (new V21IswRecord(
            workTitle: 'Assigned Work',
            submitterWorkNumber: 'WORK123',
            mwDistributionCategory: 'POP',
            versionType: 'ORI',
            iswc: 'T3402983517',
        ))->setRecordPrefix(0, 0)->toString();

        $parsed = V21IswRecord::parseLine($line);

        expect($parsed['work_title'])->toBe('ASSIGNED WORK')
            ->and($parsed['submitter_work_number'])->toBe('WORK123')
            ->and($parsed['iswc'])->toBe('T3402983517');
    });
});
