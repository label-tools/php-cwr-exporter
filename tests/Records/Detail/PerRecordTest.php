<?php

use LabelTools\PhpCwrExporter\Records\Detail\PerRecord;

describe('PER (Performing Artist) Record', function () {
    it('builds a minimal record from required fields', function () {
        $record = new PerRecord('Smith');
        $str = $record->setRecordPrefix(0, 0)->toString();

        expect(substr($str, 0, 3))->toBe('PER');
        expect(trim(substr($str, 19, 45)))->toBe('SMITH');
        expect(substr($str, 64, 30))->toBe(str_repeat(' ', 30));
        expect(substr($str, 94, 11))->toBe(str_repeat(' ', 11));
        expect(substr($str, 105, 13))->toBe(str_repeat(' ', 13));
    });

    it('validates last name is required', function () {
        new PerRecord('');
    })->throws(\InvalidArgumentException::class);

    it('validates IPI name is digits', function () {
        (new PerRecord('Jones'))->setPerformingArtistIpiNameNumber('ABC');
    })->throws(\InvalidArgumentException::class);

    it('validates IPI base format', function () {
        (new PerRecord('Jones'))->setPerformingArtistIpiBaseNumber('#');
    })->throws(\InvalidArgumentException::class);
});
