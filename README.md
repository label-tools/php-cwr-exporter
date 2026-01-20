# PHP CWR

A PHP library for generating Common Works Registration (CWR) files from simple PHP arrays as well as parsing Acknowledment files.

This library simplifies the process of creating CWR files by abstracting the complex, fixed-width format into a structured, easy-to-use builder pattern. It supports **CWR v2.2** and **CWR v2.1 (revision 8)**.

Install the library via Composer:

```bash
composer require labeltools/php-cwr-exporter
```

## Basic Usage

The `CwrBuilder` provides a fluent interface to construct your CWR file. You start by selecting the CWR version, defining the sender and transaction details, then provide an array of works to be registered.

### CWR v2.2 Example

```php
use LabelTools\PhpCwrExporter\CwrBuilder;
use LabelTools\PhpCwrExporter\Enums\SenderType;
use LabelTools\PhpCwrExporter\Enums\TransactionType;
// ... other enums

$cwr = CwrBuilder::v22()
    ->senderType(SenderType::PUBLISHER)
    ->senderId('SENDER_ID')
    ->senderName('My Publishing Company')
    ->software('My Awesome App', '1.0')
    ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
    ->works([
        // ... array of work definitions
    ]);

$payload = $cwr->export();

file_put_contents('CWR_EXPORT.V22', $payload);
```

### CWR v2.1 Example

```php
use LabelTools\PhpCwrExporter\CwrBuilder;
use LabelTools\PhpCwrExporter\Enums\SenderType;
use LabelTools\PhpCwrExporter\Enums\TransactionType;
// ... other enums

$cwr = CwrBuilder::v21()
    ->senderType(SenderType::PUBLISHER)
    ->senderId('SENDER_ID')
    ->senderName('My Publishing Company')
    ->transaction(TransactionType::NEW_WORK_REGISTRATION->value)
    ->works([
        // ... array of work definitions
    ]);

$payload = $cwr->export();

file_put_contents('CWR_EXPORT.V21', $payload);
```

## Acknowledgment (ACK) Parsing

Use the ACK parser to read society acknowledgment files (ACK)

```php
use LabelTools\PhpCwrExporter\Acknowledgements\AckParser;

$payload = file_get_contents('CWR_ACK.V21');

$result = AckParser::auto()->parse($payload, [
    // Optional: lets the parser derive sender/receiver codes from the filename.
    'filename' => 'CW240001ABC_DEF.V21',
    // Optional: include raw record payloads (defaults to false).
    'include_payload' => true,
]);

$data = $result->toArray();
```

### ACK Output Structure

```
file:
  sender: { type, id, name, code? }
  receiver: { code? } | null
  creation_date
  creation_time
  version

groups[]:
  group_id
  acknowledgements[]:
    correlation:
      creation_date
      creation_time
      original_group_id
      original_transaction_sequence
      original_transaction_type
    work:
      submitter_creation_number
      recipient_creation_number
      creation_title
      transaction_type
      submitter_work_number
      iswc
    status:
      transaction_status
      processing_date
    messages[]
    payload
```

Errors during parsing raise `AckParseException` with a stable `getErrorCode()` value for tests and downstream handling.

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
| `recordings`              | `array`                                   | No       | Optional array of recording definitions.                                 |
| `performing_artists`      | `array`                                   | No       | Optional array of unique performing artist definitions; each entry emits a `PER` record capturing writers who perform the work. |

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
| `controlled`                        | `bool`                    | No       | Defaults to `true`. Set `false` to emit an OWR (uncontrolled writer) instead of SWR/SWT. |
| `publisher_interested_party_number` | `string`                  | No       | The IP number of the publisher representing this writer. **Required to create a PWR link.** |
| `ipi_name_number`                   | `string`                  | No       | The writer's IPI Name Number.                                            |
| `pr_affiliation_society`            | `SocietyCode` enum        | No       | The writer's Performing Rights society.                                  |
| `territories`                       | `array`                   | No       | An array of writer territory definitions.                                |

---

### Other Writer Definition (OWR)

If `controlled` is `false`, the writer is rendered as an `OWR` record (same layout as `SWR` but without SWT/PWR). The same fields as above apply.

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
| `name`                       | `string`             | No       | Required for controlled publishers; leave blank when `controlled` is `false` (emits an `OPU`). |
| `type`                       | `PublisherType` enum | No       | Required for controlled publishers; optional for `OPU` records.          |
| `controlled`                 | `bool`               | No       | Defaults to `true`. Set `false` to emit an `OPU` (Other Publisher) record instead of `SPU`. |
| `ipi_name_number`            | `string`             | No       | Required for controlled publishers; optional for `OPU` records.         |
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

### Recording Definition

| Key                          | Type                 | Required | Description                                                              |
|------------------------------|----------------------|----------|--------------------------------------------------------------------------|
| `first_release_date`         | `string` (YYYYMMDD)  | No       | Date the work was first released publicly.                                |
| `first_release_duration`     | `string` (HHMMSS)    | No       | Duration of the first release (HHMMSS).                                  |
| `first_album_title`          | `string`             | No       | Album title where the work first appeared.                               |
| `first_album_label`          | `string`             | No       | Label that released the first album.                                     |
| `first_release_catalog_number` | `string`           | No       | Internal catalog number for the first release.                           |
| `first_release_ean`          | `string`             | No       | EAN-13 code for the release.                                             |
| `first_release_isrc`         | `string`             | No       | ISRC for the recording of the work.                                      |
| `recording_format`           | `string`             | No       | “A” for audio or “V” for video.                                          |
| `recording_technique`        | `string`             | No       | “A” Analogue, “D” Digital, “U” Unknown.                                  |
| `media_type`                 | `string` (3)         | No       | BIEM/CISAC media type code.                                              |
|                              |                      |          | Each recording definition must populate at least one of the optional fields. |

---

### Performing Artist Definition

| Key                      | Type     | Required | Description                                              |
|--------------------------|----------|----------|----------------------------------------------------------|
| `last_name`              | `string` | Yes      | Artist's last name (or single name) (creates a `PER`).   |
| `first_name`             | `string` | No       | Artist's first name.                                     |
| `ipi_name_number`        | `string` | No       | 11-digit IPI Name Number.                                |
| `ipi_base_number`        | `string` | No       | IPI Base Number (1–13 alphanumeric characters).         |

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
