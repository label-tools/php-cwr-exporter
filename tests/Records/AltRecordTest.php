<?php

use LabelTools\PhpCwrExporter\Records\AltRecord;
use LabelTools\PhpCwrExporter\Enums\TitleType;
use LabelTools\PhpCwrExporter\Enums\LanguageCode;

describe('ALT (Alternate Title) Record', function () {
    describe('Base', function () {
        it('builds a minimal valid ALT record with correct padding & format', function () {
            $record = new AltRecord(
                alternateTitle: 'My Alternate Title',
                titleType: TitleType::FORMAL_TITLE
            );

            $out = $record->toString();

            // Total length = 19 (prefix) + 60 (title) + 2 (type) + 2 (lang) = 83
            expect(strlen($out))->toBe(83);

            // 0–2:   "ALT"
            expect(substr($out, 0, 3))->toBe('ALT');

            // 3–18: 16 spaces
            expect(substr($out, 3, 16))->toBePadded('', 16);

            // 19–78: Alternate Title (60 chars)
            expect(substr($out, 19, 60))->toBePadded('My Alternate Title', 60);

            // 79–80: Title Type (2 chars) => FT
            expect(substr($out, 79, 2))->toBe(TitleType::FORMAL_TITLE->value);

            // 81–82: Language Code (2 chars) => two spaces
            expect(substr($out, 81, 2))->toBePadded('', 2);
        });

        it('throws when Alternate Title is empty', function () {
            new AltRecord(
                alternateTitle: '',
                titleType: TitleType::FORMAL_TITLE
            );
        })->throws(
            \InvalidArgumentException::class,
            'Alternate Title must be 1–60 characters.'
        );

        it('throws when Alternate Title exceeds 60 characters', function () {
            new AltRecord(
                alternateTitle: str_repeat('A', 61),
                titleType: TitleType::FORMAL_TITLE
            );
        })->throws(
            \InvalidArgumentException::class,
            'Alternate Title must be 1–60 characters.'
        );

        it('throws when Title Type requires Language Code but none provided', function () {
            $record = new AltRecord(
                alternateTitle: 'Título',
                titleType: TitleType::ORIGINAL_TITLE_NATIONAL_CHARACTERS
            );
            $record->toString();
        })->throws(
            \InvalidArgumentException::class,
            "ALT: Language Code is required when Title Type is 'OL'."
        );

        it('accepts national-character types when Language Code is provided', function () {
            $record = new AltRecord(
                alternateTitle: 'Título',
                titleType: TitleType::ORIGINAL_TITLE_NATIONAL_CHARACTERS,
                languageCode: LanguageCode::SPANISH
            );

            $out = $record->toString();

            expect(strlen($out))->toBe(83);
            expect(substr($out, 79, 2))->toBe(TitleType::ORIGINAL_TITLE_NATIONAL_CHARACTERS->value);
            expect(substr($out, 81, 2))->toBePadded('ES', 2);
        });
    });
});