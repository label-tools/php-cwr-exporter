<?php

use LabelTools\PhpCwrExporter\Records\RevRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\RevRecord as V21RevRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\RevRecord as V22RevRecord;

describe('Revised Registration (REV) Record', function () {
    describe('Main', function () {
        it('builds a valid REV record', function () {
            $record = (new RevRecord('Title','ABC','POP','ORI'));
            $record = $record->toString();

            expect(strlen($record))->toBe(259);
            expect(substr($record, 0, 19))->toBe(str_pad('REV', 19, ' '));
        });
    });

     describe('CWR v2.1', function () {
        it('builds a valid NWR record for CWR v2.1', function () {
            $record = (new V21RevRecord('Title','ABC','POP','ORI'));
            $record = $record->toString();

            expect(strlen($record))->toBe(260);
            expect(substr($record, 0, 19))->toBe(str_pad('REV', 19, ' '));

        });
    });

    describe('CWR v2.2', function () {
        it('builds a valid NWR record for CWR v2.2', function () {
            $record = (new V22RevRecord('Title','ABC','POP','ORI'));
            $record = $record->toString();

            expect(strlen($record))->toBe(260);
            expect(substr($record, 0, 19))->toBe(str_pad('REV', 19, ' '));

        });
    });
});