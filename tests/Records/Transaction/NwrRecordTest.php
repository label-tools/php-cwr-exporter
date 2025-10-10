<?php

use LabelTools\PhpCwrExporter\Records\Transaction\NwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\NwrRecord as V21NwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\Transaction\NwrRecord as V22NwrRecord;

describe('New Work Registration (NWR) Record', function () {
    describe('Record format', function () {
        it('builds valid record with required fields', function () {
            $record = new NwrRecord(
                workTitle: 'My Song Title',
                submitterWorkNumber: 'Sub123',
                mwDistributionCategory: 'POP',
                versionType: 'ORI'
            );

            $str = $record->setRecordPrefix(0,1)->toString();
            expect(strlen($str))->toBe(259);

            // Prefix (19 A)
            expect(substr($str, 0, 3))->toBe('NWR');
            expect(substr($str, 3, 8))->toBePaddedLeft('0', 8, '0');
            expect(substr($str, 11, 8))->toBePaddedLeft('1', 8, '0');

            // Title (60 A)
            expect(substr($str, 19, 60))->toBePadded('MY SONG TITLE', 60);
            // Language Code (defaults to spaces)
            expect(substr($str, 79, 2))->toBePadded('', 2);
            // Submitter Work # (14 A)
            expect(substr($str, 81, 14))->toBePadded('SUB123', 14);
            // ISWC (defaults to empty)
            expect(substr($str, 95, 11))->toBePadded('', 11);
            // Copyright Date (defaults to zeros)
            expect(substr($str, 106, 8))->toBePadded('', 8);
            // Copyright Number
            expect(substr($str, 114, 12))->toBePadded('', 12);
            // Distribution Category
            expect(substr($str, 126, 3))->toBe('POP');
            // Duration (defaults to zeros)
            expect(substr($str, 129, 6))->toBePadded('', 6);
            // Recorded Indicator (defaults to space)
            expect(substr($str, 135, 1))->toBe(' ');
            // Text Music Relationship (defaults to spaces)
            expect(substr($str, 136, 3))->toBePadded('', 3);
            // Composite Type
            expect(substr($str, 139, 3))->toBePadded('', 3);
            // Version Type
            expect(substr($str, 142, 3))->toBe('ORI');
            // Excerpt Type
            expect(substr($str, 145, 3))->toBePadded('', 3);
            // Music Arrangement
            expect(substr($str, 148, 3))->toBePadded('', 3);
            // Lyric Adaptation
            expect(substr($str, 151, 3))->toBePadded('', 3);
            // Contact Name
            expect(substr($str, 154, 30))->toBePadded('', 30);
            // Contact ID
            expect(substr($str, 184, 10))->toBePadded('', 10);
            // CWR Work Type
            expect(substr($str, 194, 2))->toBePadded('', 2);
            // Grand Rights Indicator
            expect(substr($str, 196, 1))->toBe(' ');
            // Composite Component Count
            expect(substr($str, 197, 3))->toBe('000');
            // Publication Date
            expect(substr($str, 200, 8))->toBePadded('', 8);
            // Exceptional Clause
            expect(substr($str, 208, 1))->toBe(' ');
            // Opus Number
            expect(substr($str, 209, 25))->toBePadded('', 25);
            // Catalogue Number
            expect(substr($str, 234, 25))->toBePadded('', 25);
        });
    });

    it('throws when setRecordPrefix is not called', function () {
        $record = new NwrRecord(
            workTitle: 'My Song Title',
            submitterWorkNumber: 'SUB123',
            mwDistributionCategory: 'POP',
            versionType: 'ORI'
        );

        $record->toString();
    })->throws(\LogicException::class, 'The record prefix for');

    describe('Field-level validation', function () {
        it('throws when Work Title is empty', function () {
            new NwrRecord(
                workTitle: '',
                submitterWorkNumber: 'ABC123',
                mwDistributionCategory: 'POP',
                versionType: 'ORI'
            );
        })->throws(InvalidArgumentException::class);

        it('throws when Work Title contains non-ASCII', function () {
            new NwrRecord(
                workTitle: "Título ñ",
                submitterWorkNumber: 'ABC123',
                mwDistributionCategory: 'POP',
                versionType: 'ORI'
            );
        })->throws(InvalidArgumentException::class);

        it('throws when Language Code is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setLanguageCode('XX');
        })->throws(InvalidArgumentException::class);

        it('throws when Submitter Work Number is empty', function () {
            new NwrRecord(
                workTitle: 'Title',
                submitterWorkNumber: '',
                mwDistributionCategory: 'POP',
                versionType: 'ORI'
            );
        })->throws(InvalidArgumentException::class);

        it('throws when ISWC is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setIswc('INVALID');
        })->throws(InvalidArgumentException::class);

        it('throws when Copyright Date is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setCopyrightDate('2025-1-1');
        })->throws(InvalidArgumentException::class);

        it('throws when MW Distribution Category is empty', function () {
            new NwrRecord(
                workTitle: 'Title',
                submitterWorkNumber: 'ABC',
                mwDistributionCategory: '',
                versionType: 'ORI'
            );
        })->throws(InvalidArgumentException::class);

        it('throws when MW Distribution Category is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setMwDistributionCategory('XXX');
        })->throws(InvalidArgumentException::class);

        it('throws when Duration is invalid format', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setDuration('ABCDEF');
        })->throws(InvalidArgumentException::class);

        it('throws when Duration is zero for SER category', function () {
            (new NwrRecord('Title','ABC','SER','ORI'))
                ->setDuration('000000');
        })->throws(InvalidArgumentException::class);

        it('throws when Recorded Indicator is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setRecordedIndicator('Z');
        })->throws(InvalidArgumentException::class);

        it('throws when Text Music Relationship is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setTextMusicRelationship('XYZ');
        })->throws(InvalidArgumentException::class);

        it('throws when Composite Type is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setCompositeType('XXX');
        })->throws(InvalidArgumentException::class);

        it('throws when Version Type is empty', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setVersionType('');
        })->throws(InvalidArgumentException::class);

        it('throws when Version Type is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setVersionType('XXX');
        })->throws(InvalidArgumentException::class);

        it('throws when Excerpt Type is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setExcerptType('XXX');
        })->throws(InvalidArgumentException::class);

        it('throws when Version is MOD without Music Arrangement', function () {
            $rec = new NwrRecord('Title','ABC','POP','MOD');
            $rec->setCompositeType('');
            $rec->setLyricAdaptation('');
            $rec->setMusicArrangement('');
        })->throws(InvalidArgumentException::class);

        it('throws when Version is MOD without Lyric Adaptation', function () {
            $rec = new NwrRecord('Title','ABC','POP','MOD');
            $rec->setMusicArrangement('NEW');
            $rec->setLyricAdaptation('');
        })->throws(InvalidArgumentException::class);

        it('throws when Grand Rights Indicator is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setGrandRightsInd('X');
        })->throws(InvalidArgumentException::class);

        it('throws when CWR Work Type is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setCwrWorkType('XX');
        })->throws(InvalidArgumentException::class);

        it('throws when Composite Type present but Component Count missing', function () {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage("Composite Type is set but Component Count is missing.");
            $rec = new NwrRecord('Title','ABC','POP','ORI');
            $rec->setCompositeType('COS');
            $rec->setCompositeComponentCount(0);
            $rec->setRecordPrefix(0,0)->toString();
        });

        it('throws when Component Count present but Composite Type missing', function () {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage("Component Count is set but Composite Type is missing.");
            $rec = (new NwrRecord('Title','ABC','POP','ORI'))
                ->setRecordPrefix(0,0)
                ->setCompositeComponentCount(2);
            $rec->toString();
        });

        it('throws when Publication Date is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setPublicationDate('2025-1-1');
        })->throws(InvalidArgumentException::class);

        it('throws when Exceptional Clause is invalid', function () {
            (new NwrRecord('Title','ABC','POP','ORI'))
                ->setExceptionalClause('X');
        })->throws(InvalidArgumentException::class);
    });

    describe('CWR v2.1', function () {
        it('builds a valid NWR record for CWR v2.1', function () {
            $record = (new V21NwrRecord('Title','ABC','POP','ORI'));
            $record = $record->setRecordPrefix(0,0)->toString();

            expect(strlen($record))->toBe(260);
            expect(substr($record, 259, 1))->toBe(' ');

        });
    });

    describe('CWR v2.2', function () {
        it('builds a valid NWR record for CWR v2.2', function () {
            $record = (new V22NwrRecord('Title','ABC','POP','ORI'))
                ->setPriorityFlag(true);
            $record = $record->setRecordPrefix(0,0)->toString();

            expect(strlen($record))->toBe(260);
            expect(substr($record, 259, 1))->toBe('Y');

        });
    });
});