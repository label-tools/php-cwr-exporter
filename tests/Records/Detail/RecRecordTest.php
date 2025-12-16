<?php

use LabelTools\PhpCwrExporter\Records\Detail\RecRecord;

describe('REC (Recording Detail) Record', function () {
    it('builds a minimal valid REC record with padding', function () {
        $record = new RecRecord('20240101');
        $str = $record->setRecordPrefix(0, 0)->toString();

        expect(strlen($str))->toBe(266);
        expect(substr($str, 0, 3))->toBe('REC');
        expect(substr($str, 19, 8))->toBe('20240101'); // First Release Date
        expect(substr($str, 27, 60))->toBe(str_repeat(' ', 60)); // Constant blank
        expect(substr($str, 87, 6))->toBe(str_repeat(' ', 6)); // Duration default blank
    });

    it('handles string fields and segments properly', function () {
        $record = (new RecRecord(
            firstReleaseDate: '20240102',
            firstAlbumTitle: 'Greatest Hits',
            firstAlbumLabel: 'Big Label',
            firstReleaseCatalogNumber: 'CAT123',
            firstReleaseEan: '0001234567890',
            firstReleaseIsrc: 'USRC17607839',
            recordingFormat: 'A',
            recordingTechnique: 'D',
            mediaType: 'AUD'
        ))->setFirstReleaseDuration('000300');

        $str = $record->setRecordPrefix(0, 0)->toString();
        expect(substr($str, 19, 8))->toBe('20240102');
        expect(substr($str, 27 + 0, 60))->toBe(str_repeat(' ', 60));
        expect(substr($str, 87, 6))->toBe('000300');
        expect(substr($str, 93, 5))->toBe(str_repeat(' ', 5));
        expect(trim(substr($str, 98, 60)))->toBe('GREATEST HITS');
        expect(trim(substr($str, 158, 60)))->toBe('BIG LABEL');
        expect(trim(substr($str, 218, 18)))->toBe('CAT123');
        expect(trim(substr($str, 236, 13)))->toBe('0001234567890');
        expect(trim(substr($str, 249, 12)))->toBe('USRC17607839');
        expect(substr($str, 261, 1))->toBe('A');
        expect(substr($str, 262, 1))->toBe('D');
        expect(substr($str, 263, 3))->toBe('AUD');
    });

    it('rejects a record with no data', function () {
        (new RecRecord())->setRecordPrefix(0, 0)->toString();
    })->throws(\InvalidArgumentException::class);

    it('validates recording format values', function () {
        (new RecRecord())->setRecordingFormat('Z');
    })->throws(\InvalidArgumentException::class);

    it('validates recording technique values', function () {
        (new RecRecord())->setRecordingTechnique('X');
    })->throws(\InvalidArgumentException::class);

    it('validates EAN format', function () {
        (new RecRecord())->setFirstReleaseEan('123');
    })->throws(\InvalidArgumentException::class);

    it('validates ISRC format', function () {
        (new RecRecord())->setFirstReleaseIsrc('123456');
    })->throws(InvalidArgumentException::class);
});
