<?php

use LabelTools\PhpCwrExporter\Records\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\HdrRecord as V21HdrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\HdrRecord as V22HdrRecord;

describe('Transmission Header Record', function () {
    describe('Main', function () {
        it('builds a valid HDR record according to the spec', function () {
            $hdr = new HdrRecord(
                senderType: 'PB',
                senderId: '123456789',
                senderName: 'Test Publisher Name',
                creationDate: '20240527',
                creationTime: '153000',
                transmissionDate: '20240527',
            );

            $record = $hdr->toString();

            expect(strlen($record))->toBe(86);

            expect(substr($record, 0, 3))->toBe('HDR');               // Record Type
            expect(substr($record, 3, 2))->toBe('PB');                // Sender Type
            expect(substr($record, 5, 9))->toBe('123456789');         // Sender ID
            expect(substr($record, 14, 45))->toBe(str_pad('Test Publisher Name', 45)); // Sender Name
            expect(substr($record, 59, 5))->toBe('01.10');            // EDI Version
            expect(substr($record, 64, 8))->toBe('20240527');         // Creation Date
            expect(substr($record, 72, 6))->toBe('153000');           // Creation Time
            expect(substr($record, 78, 8))->toBe('20240527');         // Transmission Date
        });

        // -- Invalid Sender Type --
        it('throws for an invalid sender type', function () {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage("Invalid sender type");
            new HdrRecord('XX', '123456789', 'Valid Name');
        });

        // -- Invalid Sender ID for numeric case --
        it('throws for a non-numeric or wrong-length sender ID when Sender Type is normal', function () {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage("Sender ID must be numeric and exactly 9 digits when Sender Type is not PB, AA, or WR.");
            new HdrRecord('SO', '86585686578678', 'Valid Name');
        });

        // -- Workaround case: 11-digit IPI with PB --
        it('handles an 11-digit sender ID with PB by splitting type and id', function () {
            $hdr = new HdrRecord('PB', '99123456789', 'Valid Name');
            $record = $hdr->toString();
            expect(substr($record, 3, 2))->toBe('99');
            expect(substr($record, 5, 9))->toBe('123456789');
        });

        // -- Sender Name too long --
        it('throws when sender name exceeds 45 characters', function () {
            $long = str_repeat('A', 46);
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage("Sender Name must not exceed 45 characters.");
            new HdrRecord('PB', '123456789', $long);
        });

        // -- Invalid Creation Date format --
        it('throws for invalid creation date format', function () {
            new HdrRecord('PB', '123456789', 'Valid Name', '20-01-01', null, null);
        })->throws(InvalidArgumentException::class, "Creation Date must be a valid date in 'YYYYMMDD' format.");

        // -- Invalid Transmission Date format --
        it('throws for invalid transmission date format', function () {
            new HdrRecord('PB', '123456789', 'Valid Name', null, null, '2025/01/01');
        })->throws(InvalidArgumentException::class, "Transmission Date must be a valid date in 'YYYYMMDD' format.");
    });

    describe('CWR v2.1', function () {
        // -- Default (no charset) produces 15 spaces in the charset slot --
        it('builds a v2.1 HDR and leaves charset blank when none given', function () {
            $hdr = new V21HdrRecord(
                senderType:     'PB',
                senderId:       '123456789',
                senderName:     'Publisher',
                creationDate:   '20240527',
                creationTime:   '120000',
                transmissionDate:'20240528'
                // no charset argument
            );

            $record = $hdr->toString();
            expect(strlen($record))->toBe(101);

            // Charset lives at byte 87–101 (1-based), i.e. offset 86, length 15
            expect(substr($record, 86, 15))->toBe(str_repeat(' ', 15));
        });

        // -- Specified charset is padded/truncated to 15 chars --
        it('inserts the provided charset into the 15-char slot', function () {
            $hdr = new V21HdrRecord(
                'PB',
                '123456789',
                'Publisher',
                '20240527',
                '120000',
                '20240528',
                'UTF-8'
            );

            $record = $hdr->toString();
            // Should appear left-aligned and space-padded to 15
            expect(substr($record, 86, 15))->toBe(str_pad('UTF-8', 15));
        });

        // -- Charset too long is rejected --
        it('throws if the charset string is not one of the accepted', function () {
            $long = str_repeat('X', 16);

            new V21HdrRecord(
                'PB',
                '123456789',
                'Publisher',
                '20240527',
                '120000',
                '20240528',
                $long
            );
        })->throws(InvalidArgumentException::class, "Character Set must be one of 'ASCII', 'UTF-8', 'ISO-8859-1'.");
    });

    describe('CWR v2.2', function () {
        // -- v2.2: default padding of new fields --
        it('builds a v2.2 HDR and leaves all new fields blank when none given', function () {
            $hdr = new V22HdrRecord(
                senderType:       'PB',
                senderId:         '123456789',
                senderName:       'Publisher',
                creationDate:     '20240527',
                creationTime:     '120000',
                transmissionDate: '20240528',
            );

            $record = $hdr->toString();
            expect(strlen($record))->toBe(167);

            // Version field at bytes 102-104 (0-based offset 101, length 3)
            expect(substr($record, 101, 3))->toBe(str_repeat(' ', 3));
            // Revision field at bytes 105-107 (offset 104, length 3)
            expect(substr($record, 104, 3))->toBe(str_repeat(' ', 3));
            // Software Package at bytes 108-137 (offset 107, length 30)
            expect(substr($record, 107, 30))->toBe(str_repeat(' ', 30));
            // Software Package Version at bytes 138-167 (offset 137, length 30)
            expect(substr($record, 137, 30))->toBe(str_repeat(' ', 30));
        });

        // -- v2.2: inserting version & revision --
        it('inserts version and revision properly', function () {
            $hdr = new V22HdrRecord(
                senderType:       'PB',
                senderId:         '123456789',
                senderName:       'Publisher',
                creationDate:     '20240527',
                creationTime:     '120000',
                transmissionDate: '20240528',
                characterSet:     null,
                version:          '2.2',
                revision:         '1',
            );

            $record = $hdr->toString();
            expect(substr($record, 101, 3))->toBe('2.2');
            expect(substr($record, 104, 3))->toBe('1  ');
        });

        // -- v2.2: inserting software package & its version --
        it('inserts software package and version padded to 30 chars', function () {
            $hdr = new V22HdrRecord(
                senderType:                'PB',
                senderId:                  '123456789',
                senderName:                'Publisher',
                creationDate:              '20240527',
                creationTime:              '120000',
                transmissionDate:          '20240528',
                characterSet:              null,
                version:                   null,
                revision:                  null,
                softwarePackage:           'MySoftware',
                softwarePackageVersion:    'v1.0.0',
            );

            $record = $hdr->toString();
            expect(substr($record, 107, 30))->toBe(str_pad('MySoftware', 30));
            expect(substr($record, 137, 30))->toBe(str_pad('v1.0.0', 30));
        });

        // -- v2.2 validation errors --

        it('throws when version exceeds 3 characters', function () {
            new V22HdrRecord(
                senderType:       'PB',
                senderId:         '123456789',
                senderName:       'Publisher',
                creationDate:     '20240527',
                creationTime:     '120000',
                transmissionDate: '20240528',
                characterSet:     null,
                version:          '1234',
            );
        })->throws(InvalidArgumentException::class, "Version must be in format 'X.Y' where X and Y are numbers.");

        it('throws when revision is not a 3-digit numeric', function () {
            new V22HdrRecord(
                senderType:       'PB',
                senderId:         '123456789',
                senderName:       'Publisher',
                creationDate:     '20240527',
                creationTime:     '120000',
                transmissionDate: '20240528',
                characterSet:     null,
                version:          null,
                revision:         '32112',
            );
        })->throws(InvalidArgumentException::class, 'Revision must be a number between 0 and 999.');

        it('throws when software package exceeds 30 characters', function () {
            new V22HdrRecord(
                senderType:       'PB',
                senderId:         '123456789',
                senderName:       'Publisher',
                creationDate:     '20240527',
                creationTime:     '120000',
                transmissionDate: '20240528',
                softwarePackage:  str_repeat('X', 31),
            );
        })->throws(InvalidArgumentException::class, 'Software Package must be at most 30 characters long.');

        it('throws when software package version exceeds 30 characters', function () {
            new V22HdrRecord(
                senderType:               'PB',
                senderId:                 '123456789',
                senderName:               'Publisher',
                creationDate:             '20240527',
                creationTime:             '120000',
                transmissionDate:         '20240528',
                softwarePackageVersion:   str_repeat('Y', 31),
            );
        })->throws(InvalidArgumentException::class, 'Software Package Version must be at most 30 characters long.');
    });
});