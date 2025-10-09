# PHP CWR Exporter

A PHP library for generating Common Works Registration (CWR) files from simple PHP arrays.

This library simplifies the process of creating CWR files by abstracting the complex, fixed-width format into a structured, easy-to-use builder pattern.

> **Note:** This library currently only supports **CWR v2.2**.

## Installation

Install the library via Composer:

```bash
composer require labeltools/php-cwr-exporter
```

## Basic Usage

The `CwrBuilder` provides a fluent interface to construct your CWR file. You start by defining the sender and transaction details, then provide an array of works to be registered.

```php
use LabelTools\PhpCwrExporter\CwrBuilder;
use LabelTools\PhpCwrExporter\Enums\SenderType;
use LabelTools\PhpCwrExporter\Enums\TransactionType;
use LabelTools\PhpCwrExporter\Enums\MusicalWorkDistributionCategory;
use LabelTools\PhpCwrExporter\Enums\PublisherType;
use LabelTools\PhpCwrExporter\Enums\TitleType;
use LabelTools\PhpCwrExporter\Enums\VersionType;
use LabelTools\PhpCwrExporter\Enums\WriterDesignation;

$cwr = CwrBuilder::v22()
    ->senderType(SenderType::PUBLISHER)
    ->senderId('SENDER_ID')
    ->senderName('My Publishing Company')
    ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
    ->works([
        // ... array of work definitions
    ]);

$payload = $cwr->export();

file_put_contents('CWR_EXPORT.V22', $payload);
```

## Data Structure

The `works()` method accepts an array of work definitions. Each work is an associative array containing details about the composition, its writers, and its publishers.

### Work Definition

| Key                       | Type                                      | Required | Description                                                              |
|---------------------------|-------------------------------------------|----------|--------------------------------------------------------------------------|
| `submitter_work_number`   | `string`                                  | Yes      | Your unique identifier for the work.                                     |
| `title`                   | `string`                                  | Yes      | The primary title of the work.                                           |
| `title_type`              | `TitleType` enum                          | Yes      | The type of the primary title (e.g., `TitleType::ORIGINAL_TITLE`).       |
| `distribution_category`   | `MusicalWorkDistributionCategory` enum    | Yes      | The distribution category (e.g., `MusicalWorkDistributionCategory::POPULAR`). |
| `version_type`            | `VersionType` enum                        | Yes      | The version of the work (e.g., `VersionType::ORIGINAL_WORK`).            |
| `language`                | `LanguageCode` enum                       | No       | The primary language of the work.                                        |
| `iswc`                    | `string`                                  | No       | International Standard Musical Work Code.                                |
| `copyright_date`          | `string` (YYYYMMDD)                       | No       | The date of copyright.                                                   |
| `copyright_number`        | `string`                                  | No       | The copyright registration number.                                       |
| `duration`                | `string` (HHMMSS)                         | No       | The duration of the work.                                                |
| `recorded`                | `bool`                                    | No       | `true` if the work has been recorded.                                    |
| `text_music_relationship` | `string`                                  | No       | The relationship between text and music.                                 |
| `alternate_titles`        | `array`                                   | No       | An array of alternate title definitions.                                 |
| `writers`                 | `array`                                   | Yes      | An array of writer definitions.                                          |
| `publishers`              | `array`                                   | Yes      | An array of publisher definitions.                                       |

---

### Alternate Title Definition

| Key               | Type                | Required | Description                                                |
|-------------------|---------------------|----------|------------------------------------------------------------|
| `alternate_title` | `string`            | Yes      | The alternate title.                                       |
| `title_type`      | `TitleType` enum    | Yes      | The type of the alternate title (e.g., `TitleType::ALTERNATIVE_TITLE`). |
| `language_code`   | `LanguageCode` enum | No       | The language of this alternate title.                      |

---

### Writer Definition

| Key                                 | Type                      | Required | Description                                                              |
|-------------------------------------|---------------------------|----------|--------------------------------------------------------------------------|
| `interested_party_number`           | `string`                  | Yes      | The writer's IPI or internal ID.                                         |
| `last_name`                         | `string`                  | Yes      | The writer's last name or full legal name.                               |
| `first_name`                        | `string`                  | No       | The writer's first name.                                                 |
| `designation_code`                  | `WriterDesignation` enum  | Yes      | The writer's role (e.g., `WriterDesignation::COMPOSER_AUTHOR`).          |
| `publisher_interested_party_number` | `string`                  | No       | The IP number of the publisher representing this writer. **Required to create a PWR link.** |
| `ipi_name_number`                   | `string`                  | No       | The writer's IPI Name Number.                                            |
| `pr_affiliation_society`            | `SocietyCode` enum        | No       | The writer's Performing Rights society.                                  |
| `territories`                       | `array`                   | No       | An array of writer territory definitions.                                |

---

### Writer Territory Definition

| Key                             | Type     | Required | Description                                                              |
|---------------------------------|----------|----------|--------------------------------------------------------------------------|
| `tis_code`                      | `string` | Yes      | The TIS numeric code for the territory (e.g., `213` for World).          |
| `inclusion_exclusion_indicator` | `string` | No       | 'I' for inclusion, 'E' for exclusion. Defaults to 'I'.                   |
| `pr_collection_share`           | `float`  | No       | The writer's Performing Rights collection share for this territory.      |
| `mr_collection_share`           | `float`  | No       | The writer's Mechanical Rights collection share for this territory.      |
| `sr_collection_share`           | `float`  | No       | The writer's Synchronization Rights collection share for this territory. |

---

### Publisher Definition

| Key                          | Type                 | Required | Description                                                              |
|------------------------------|----------------------|----------|--------------------------------------------------------------------------|
| `interested_party_number`    | `string`             | Yes      | The publisher's IPI or internal ID.                                      |
| `name`                       | `string`             | Yes      | The publisher's name.                                                    |
| `type`                       | `PublisherType` enum | Yes      | The publisher's role (e.g., `PublisherType::ORIGINAL_PUBLISHER`).        |
| `ipi_name_number`            | `string`             | Yes      | The publisher's IPI Name Number.                                         |
| `tax_id`                     | `string`             | No       | The publisher's tax identification number.                               |
| `submitter_agreement_number` | `string`             | No       | Your agreement number with the publisher.                                |
| `pr_affiliation_society`     | `SocietyCode` enum   | No       | The publisher's Performing Rights society.                               |
| `pr_ownership_share`         | `float`              | No       | The publisher's Performing Rights ownership share.                       |
| `mr_affiliation_society`     | `SocietyCode` enum   | No       | The publisher's Mechanical Rights society.                               |
| `mr_ownership_share`         | `float`              | No       | The publisher's Mechanical Rights ownership share.                       |
| `sr_affiliation_society`     | `SocietyCode` enum   | No       | The publisher's Synchronization Rights society.                          |
| `sr_ownership_share`         | `float`              | No       | The publisher's Synchronization Rights ownership share.                  |
| `territories`                | `array`              | No       | An array of publisher territory definitions.                             |

---

### Publisher Territory Definition

| Key                             | Type     | Required | Description                                                              |
|---------------------------------|----------|----------|--------------------------------------------------------------------------|
| `tis_code`                      | `string` | Yes      | The TIS numeric code for the territory (e.g., `213` for World).          |
| `inclusion_exclusion_indicator` | `string` | No       | 'I' for inclusion, 'E' for exclusion. Defaults to 'I'.                   |
| `pr_collection_share`           | `float`  | No       | The publisher's Performing Rights collection share for this territory.   |
| `mr_collection_share`           | `float`  | No       | The publisher's Mechanical Rights collection share for this territory.   |
| `sr_collection_share`           | `float`  | No       | The publisher's Synchronization Rights collection share for this territory. |

### Example Work Data

```php
$works = [
    [
        'submitter_work_number' => 'WORK001',
        'title' => 'A GREAT SONG',
        'title_type' => TitleType::ORIGINAL_TITLE,
        'distribution_category' => MusicalWorkDistributionCategory::POPULAR,
        'version_type'=> VersionType::ORIGINAL_WORK,
        'writers' => [[
            'interested_party_number' => 'W001',
            'last_name' => 'Writer',
            'first_name' => 'Joe',
            'designation_code' => WriterDesignation::COMPOSER_AUTHOR,
            'publisher_interested_party_number' => 'P001', // This links Joe Writer to My Publishing Co
            'territories' => [[
                'tis_code' => '213', // World
                'pr_collection_share' => 50.0,
                'mr_collection_share' => 50.0,
                'sr_collection_share' => 50.0,
            ]]
        ]],
        'publishers' => [[
            'interested_party_number' => 'P001',
            'name' => 'My Publishing Co',
            'type' => PublisherType::ORIGINAL_PUBLISHER,
            'ipi_name_number' => '123456789',
            'pr_ownership_share' => 50,
            'mr_ownership_share' => 50,
            'sr_ownership_share' => 50,
            'territories' => [[
                'tis_code' => '213', // World
                'pr_collection_share' => 100.0,
                'mr_collection_share' => 100.0,
                'sr_collection_share' => 100.0,
            ]]
        ]]
    ]
];
```

