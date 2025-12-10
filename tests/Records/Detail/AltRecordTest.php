<?php

use LabelTools\PhpCwrExporter\Records\Detail\AltRecord;
use LabelTools\PhpCwrExporter\Enums\TitleType;
use LabelTools\PhpCwrExporter\Enums\LanguageCode;

describe('ALT (Alternate Title) Record', function () {
    describe('Base', function () {
        it('builds a minimal valid ALT record with correct padding & format', function () {
            $record = new AltRecord(
                alternateTitle: 'My Alternate Title',
                titleType: TitleType::FORMAL_TITLE
            );

            $out = $record->setRecordPrefix(0,0)->toString();

            // Total length = 19 (prefix) + 60 (title) + 2 (type) + 2 (lang) = 83
            expect(strlen($out))->toBe(83);

            // 0-2:   "ALT"
            expect(substr($out, 0, 3))->toBe('ALT');

            // 3-18:
            expect(substr($out, 3, 16))->toBePadded('0', 16, '0');

            // 19-78: Alternate Title (60 chars)
            expect(substr($out, 19, 60))->toBePadded('MY ALTERNATE TITLE', 60);

            // 79-80: Title Type (2 chars) => FT
            expect(substr($out, 79, 2))->toBe(TitleType::FORMAL_TITLE->value);

            // 81-82: Language Code (2 chars) => two spaces
            expect(substr($out, 81, 2))->toBePadded('', 2);
        });

        it('throws when setRecordPrefix is not called', function () {
            $record = new AltRecord(
                alternateTitle: 'My Alternate Title',
                titleType: TitleType::FORMAL_TITLE
            );

            // Not calling setRecordPrefix() before toString()
            $record->toString();
        })->throws(\LogicException::class, 'The record prefix for');

        it('throws when Alternate Title is empty', function () {
            new AltRecord(
                alternateTitle: '',
                titleType: TitleType::FORMAL_TITLE
            );
        })->throws(
            \InvalidArgumentException::class,
            'Alternate Title must be 1-60 characters: '
        );

        it('throws when Alternate Title exceeds 60 characters', function () {
            new AltRecord(
                alternateTitle: str_repeat('A', 61),
                titleType: TitleType::FORMAL_TITLE
            );
        })->throws(
            \InvalidArgumentException::class,
            'Alternate Title must be 1-60 characters: ' . str_repeat('A', 61)
        );

        it('throws when Title Type requires Language Code but none provided', function () {
            $record = new AltRecord(
                alternateTitle: 'Título',
                titleType: TitleType::ORIGINAL_TITLE_NATIONAL_CHARACTERS
            );
            $record->setRecordPrefix(0,0)->toString();
        })->throws(
            \InvalidArgumentException::class,
            "ALT: Language Code is required when Title Type is 'OL'."
        );

        it('accepts national-character types when Language Code is provided', function () {
            $record = new AltRecord(
                alternateTitle: 'TÍTULOÍÍÍÍÍ',
                titleType: TitleType::ORIGINAL_TITLE_NATIONAL_CHARACTERS,
                languageCode: LanguageCode::SPANISH
            );

            $out = $record->setRecordPrefix(0,0)->toString();


            expect(mb_strlen($out))->toBe(83);

            // 19-78: Alternate Title (60 chars)
            $title = mb_substr($out, 19, 60);

            expect(strlen($title))->toBe(66); //without mb.. will count all the bytes of non-ascii chars
            expect(mb_strlen($title))->toBe(60);
            expect(mb_substr($out, 19, 60))->toBePadded('TÍTULOÍÍÍÍÍ', 60);

            // 79-80: Title Type (2 chars)
            expect(mb_substr($out, 79, 2))->toBe(TitleType::ORIGINAL_TITLE_NATIONAL_CHARACTERS->value);

            // 81-82: Language Code (2 chars)
            expect(mb_substr($out, 81, 2))->toBe(LanguageCode::SPANISH->value);

        });

        it('throws on non-ascii title when title type is not for national characters', function () {
            $record = new AltRecord(
                alternateTitle: 'NADA MÁS',
                titleType: TitleType::ALTERNATIVE_TITLE,
            );

            $record->setRecordPrefix(0,0)->toString();
        })->throws(\InvalidArgumentException::class, "Alternate Title must be ASCII: NADA MÁS");

        it('accepts national characters in title', function () {
            $record = new AltRecord(
                alternateTitle: 'NADA MÁS',
                titleType: TitleType::ALTERNATIVE_TITLE_NATIONAL_CHARACTERS,
                languageCode: LanguageCode::SPANISH
            );

            $out = $record->setRecordPrefix(0,0)->toString();

            expect(mb_strlen($out))->toBe(83);
            expect(mb_substr($out, 19, 60))->toBePadded('NADA MÁS', 60);
            expect(mb_substr($out, 79, 2))->toBe(TitleType::ALTERNATIVE_TITLE_NATIONAL_CHARACTERS->value);
            expect(mb_substr($out, 81, 2))->toBe(LanguageCode::SPANISH->value);
        });
    });
});