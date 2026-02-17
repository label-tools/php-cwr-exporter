<?php

namespace LabelTools\PhpCwrExporter\Acknowledgements;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Records\Control\GrhRecord;
use LabelTools\PhpCwrExporter\Records\Record;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\HdrRecord as V21HdrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\MsgRecord as V21MsgRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\AckRecord as V21AckRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\NwrRecord as V21NwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\RevRecord as V21RevRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\Control\HdrRecord as V22HdrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\Detail\MsgRecord as V22MsgRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\Transaction\AckRecord as V22AckRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\Transaction\NwrRecord as V22NwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\Transaction\RevRecord as V22RevRecord;

final class AckParser
{
    private const DETAIL_RECORD_TYPES = [
        'SPU',
        'SPT',
        'OPU',
        'SWR',
        'SWT',
        'PWR',
        'OWR',
        'ALT',
        'PER',
        'REC',
    ];

    private ?string $forcedVersion;

    private function __construct(?string $forcedVersion)
    {
        $this->forcedVersion = $forcedVersion;
    }

    public static function v21(): self
    {
        return new self('2.1');
    }

    public static function v22(): self
    {
        return new self('2.2');
    }

    public static function auto(): self
    {
        return new self(null);
    }

    /**
     * @param string|resource $input
     */
    public function parse(mixed $input, array $context = []): AckParseResult
    {
        $lines = $this->readInputLines($input);
        return $this->parseLines($lines, $context);
    }

    private function readInputLines(mixed $input): array
    {
        if (is_resource($input)) {
            if (get_resource_type($input) !== 'stream') {
                throw new InvalidArgumentException('Stream input must be a stream resource.');
            }

            $lines = [];
            while (($line = fgets($input)) !== false) {
                $lines[] = rtrim($line, "\r\n");
            }
            return $lines;
        }

        if (!is_string($input)) {
            throw new InvalidArgumentException('Input must be a string payload or stream resource.');
        }

        $normalized = str_replace("\r\n", "\n", $input);
        $normalized = rtrim($normalized, "\n");
        if ($normalized === '') {
            return [];
        }

        $lines = explode("\n", $normalized);
        return array_map(static fn (string $line): string => rtrim($line, "\r\n"), $lines);
    }

    private function parseLines(array $lines, array $context): AckParseResult
    {
        if (empty($lines)) {
            throw new AckParseException('ACK_EMPTY_FILE', 'ACK payload is empty.');
        }

        $includePayload = $context['include_payload'] ?? false;
        $lineNumber = 1;
        $headerLine = array_shift($lines);
        if (substr($headerLine, 0, 3) !== 'HDR') {
            throw new AckParseException('ACK_MISSING_HDR', 'ACK payload must begin with HDR.', ['line' => $lineNumber]);
        }

        $header = $this->parseHdr($headerLine);
        $version = $this->resolveVersion($headerLine, $header);
        [$ackRecordClass, $msgRecordClass, $nwrRecordClass, $revRecordClass] = $this->resolveRecordClasses($version);

        [$senderCode, $receiverCode] = $this->parseFilenameCodes($context['filename'] ?? null);

        $sender = [
            'type' => $header['sender_type'] ?? '',
            'id' => $header['sender_id'] ?? '',
            'name' => $header['sender_name'] ?? '',
        ];

        if ($senderCode !== null) {
            $sender['code'] = $senderCode;
        }

        $receiver = $context['receiver'] ?? null;
        if ($receiver === null && $receiverCode !== null) {
            $receiver = ['code' => $receiverCode];
        }

        $file = [
            'sender' => $sender,
            'receiver' => $receiver,
            'creation_date' => $header['creation_date'] ?? '',
            'creation_time' => $header['creation_time'] ?? '',
            'version' => $version,
        ];

        $groups = [];
        $currentGroup = null;
        $currentAck = null;
        $expectedTransactionSequence = 0;
        $hasTrailer = false;

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 2;
            if ($line === '') {
                continue;
            }

            $recordType = substr($line, 0, 3);

            switch ($recordType) {
                case 'GRH':
                    if ($currentGroup !== null) {
                        throw new AckParseException('ACK_MISSING_GRT', 'GRH encountered before closing previous group.', ['line' => $lineNumber]);
                    }
                    $group = $this->parseGrh($line, $lineNumber);
                    if ($group['transaction_type'] !== 'ACK') {
                        throw new AckParseException('ACK_UNSUPPORTED_GROUP_TYPE', 'Only ACK groups are supported.', ['line' => $lineNumber, 'transaction_type' => $group['transaction_type']]);
                    }

                    $currentGroup = [
                        'group_id' => $group['group_id'],
                        'acknowledgements' => [],
                    ];
                    $expectedTransactionSequence = 0;
                    break;

                case 'GRT':
                    if ($currentGroup === null) {
                        throw new AckParseException('ACK_MISSING_GRH', 'GRT encountered before GRH.', ['line' => $lineNumber]);
                    }
                    if ($currentAck !== null) {
                        $this->finalizeAck($currentAck, $currentGroup, $lineNumber, $includePayload);
                    }
                    $groups[] = $currentGroup;
                    $currentGroup = null;
                    break;

                case 'TRL':
                    if ($currentAck !== null) {
                        $this->finalizeAck($currentAck, $currentGroup, $lineNumber, $includePayload);
                    }
                    if ($currentGroup !== null) {
                        $groups[] = $currentGroup;
                        $currentGroup = null;
                    }

                    $remaining = array_slice($lines, $index + 1);
                    foreach ($remaining as $rest) {
                        if ($rest !== '') {
                            throw new AckParseException('ACK_TRAILING_DATA', 'Data found after TRL record.', ['line' => $lineNumber + 1]);
                        }
                    }
                    $hasTrailer = true;
                    break 2;

                case 'ACK':
                    if ($currentGroup === null) {
                        throw new AckParseException('ACK_MISSING_GRH', 'ACK transaction must appear within a group.', ['line' => $lineNumber]);
                    }

                    if ($currentAck !== null) {
                        $this->finalizeAck($currentAck, $currentGroup, $lineNumber, $includePayload);
                    }

                    $prefix = $this->parseRecordPrefix($line, $lineNumber);
                    if ($prefix['record_sequence'] !== 0) {
                        throw new AckParseException('ACK_INVALID_RECORD_SEQUENCE', 'ACK transaction header must have record sequence 0.', ['line' => $lineNumber]);
                    }
                    if ($prefix['transaction_sequence'] !== $expectedTransactionSequence) {
                        throw new AckParseException('ACK_INVALID_TRANSACTION_SEQUENCE', 'ACK transaction sequence does not increment as expected.', ['line' => $lineNumber]);
                    }

                    $expectedTransactionSequence++;

                    $ackData = $ackRecordClass::parseLine($line);

                    $currentAck = [
                        'ack' => $ackData,
                        'messages' => [],
                        'transaction' => null,
                        'exception' => null,
                        'raw' => [
                            'ack' => $line,
                            'messages' => [],
                            'transaction' => null,
                            'exception' => null,
                        ],
                        'transaction_sequence' => $prefix['transaction_sequence'],
                        'expected_record_sequence' => $prefix['record_sequence'] + 1,
                        'last_record_sequence' => $prefix['record_sequence'],
                    ];
                    break;

                case 'MSG':
                    if ($currentAck === null) {
                        throw new AckParseException('ACK_MSG_OUT_OF_SEQUENCE', 'MSG record encountered without ACK transaction.', ['line' => $lineNumber]);
                    }

                    $prefix = $this->parseRecordPrefix($line, $lineNumber);
                    $this->assertContinuation($currentAck, $prefix, $lineNumber, 'MSG');

                    $message = $msgRecordClass::parseLine($line);
                    $currentAck['messages'][] = [
                        'message_type' => $message['message_type'],
                        'original_record_sequence' => $message['original_record_sequence'],
                        'record_type' => $message['record_type'],
                        'message_level' => $message['message_level'],
                        'validation_number' => $message['validation_number'],
                        'message_text' => $message['message_text'],
                    ];
                    $currentAck['raw']['messages'][] = $line;
                    break;

                case 'NWR':
                case 'REV':
                    if ($currentAck === null) {
                        throw new AckParseException('ACK_TRANSACTION_OUT_OF_SEQUENCE', 'Transaction record encountered without ACK header.', ['line' => $lineNumber]);
                    }
                    if ($currentAck['transaction'] !== null) {
                        throw new AckParseException('ACK_DUPLICATE_TRANSACTION', 'Duplicate transaction header in ACK transaction.', ['line' => $lineNumber]);
                    }

                    $prefix = $this->parseRecordPrefix($line, $lineNumber);
                    $this->assertContinuation($currentAck, $prefix, $lineNumber, $recordType);

                    $transaction = $recordType === 'REV'
                        ? $revRecordClass::parseLine($line)
                        : $nwrRecordClass::parseLine($line);
                    $transaction['record_type'] = $recordType;

                    $currentAck['transaction'] = $transaction;
                    $currentAck['raw']['transaction'] = $line;
                    break;

                case 'EXC':
                    if ($currentAck === null || $currentAck['transaction'] === null) {
                        throw new AckParseException('ACK_EXC_OUT_OF_SEQUENCE', 'EXC record must follow NWR or REV within ACK transaction.', ['line' => $lineNumber]);
                    }

                    $prefix = $this->parseRecordPrefix($line, $lineNumber);
                    $this->assertContinuation($currentAck, $prefix, $lineNumber, 'EXC');

                    $exception = $nwrRecordClass::parseLine($line);
                    $exception['record_type'] = 'EXC';
                    $currentAck['exception'] = $exception;
                    $currentAck['raw']['exception'] = $line;

                    $this->finalizeAck($currentAck, $currentGroup, $lineNumber, $includePayload);
                    break;

                default:
                    if ($this->isDetailRecord($recordType)) {
                        if ($currentAck === null) {
                            throw new AckParseException('ACK_DETAIL_OUT_OF_SEQUENCE', 'Detail record encountered without ACK transaction.', ['line' => $lineNumber, 'record_type' => $recordType]);
                        }
                        if ($currentAck['transaction'] === null) {
                            throw new AckParseException('ACK_DETAIL_BEFORE_TRANSACTION', 'Detail records must follow NWR or REV within ACK transaction.', ['line' => $lineNumber, 'record_type' => $recordType]);
                        }

                        $prefix = $this->parseRecordPrefix($line, $lineNumber);
                        $this->assertContinuation($currentAck, $prefix, $lineNumber, $recordType);
                        break;
                    }
                    throw new AckParseException('ACK_UNSUPPORTED_RECORD', 'Unsupported record type encountered in ACK file.', ['line' => $lineNumber, 'record_type' => $recordType]);
            }
        }

        if ($currentAck !== null) {
            $this->finalizeAck($currentAck, $currentGroup, $lineNumber, $includePayload);
        }

        if ($currentGroup !== null) {
            throw new AckParseException('ACK_MISSING_GRT', 'Group trailer (GRT) not found before end of file.', ['line' => $lineNumber]);
        }

        if (!$hasTrailer) {
            throw new AckParseException('ACK_MISSING_TRL', 'File trailer (TRL) not found before end of file.', ['line' => $lineNumber]);
        }

        return new AckParseResult($file, $groups);
    }

    private function parseHdr(string $line): array
    {
        return strlen($line) >= 167
            ? V22HdrRecord::parseLine($line)
            : V21HdrRecord::parseLine($line);
    }

    private function parseGrh(string $line, int $lineNumber): array
    {
        if (strlen($line) < 11) {
            throw new AckParseException('ACK_INVALID_GRH', 'GRH record is too short.', ['line' => $lineNumber]);
        }

        return GrhRecord::parseLine($line);
    }

    private function parseRecordPrefix(string $line, int $lineNumber): array
    {
        try {
            return Record::parseRecordPrefix($line);
        } catch (\InvalidArgumentException $exception) {
            throw new AckParseException('ACK_INVALID_PREFIX', $exception->getMessage(), ['line' => $lineNumber], 0, $exception);
        }
    }

    private function assertContinuation(array &$currentAck, array $prefix, int $lineNumber, string $recordType): void
    {
        if ($prefix['transaction_sequence'] !== $currentAck['transaction_sequence']) {
            throw new AckParseException('ACK_SEQUENCE_CONTINUATION', 'Transaction sequence must remain constant within ACK transaction.', ['line' => $lineNumber, 'record_type' => $recordType]);
        }

        if (in_array($recordType, ['NWR', 'REV'], true) && $prefix['record_sequence'] === 0) {
            // Some societies reset the record sequence at the NWR/REV header in ACK files.
            $currentAck['last_record_sequence'] = 0;
            $currentAck['expected_record_sequence'] = 1;
            return;
        }

        if ($prefix['record_sequence'] !== $currentAck['expected_record_sequence']) {
            throw new AckParseException('ACK_RECORD_CONTINUATION', 'Record sequence must continue within ACK transaction.', ['line' => $lineNumber, 'record_type' => $recordType]);
        }

        $currentAck['last_record_sequence'] = $prefix['record_sequence'];
        $currentAck['expected_record_sequence'] = $prefix['record_sequence'] + 1;
    }

    private function finalizeAck(?array &$currentAck, ?array &$currentGroup, int $lineNumber, bool $includePayload): void
    {
        if ($currentAck === null) {
            return;
        }

        if ($currentGroup === null) {
            throw new AckParseException('ACK_MISSING_GRH', 'ACK transaction without group context.', ['line' => $lineNumber]);
        }

        if ($currentAck['transaction'] === null) {
            $originalType = strtoupper(trim((string) ($currentAck['ack']['original_transaction_type'] ?? '')));
            $currentAck['transaction'] = [
                'record_type' => $originalType !== '' ? $originalType : 'NWR',
                'work_title' => $currentAck['ack']['creation_title'] ?? '',
                'submitter_work_number' => $currentAck['ack']['submitter_creation_number'] ?? '',
                'iswc' => '',
            ];
        }

        $ack = $currentAck['ack'];
        $missing = [];
        foreach (['creation_date', 'creation_time', 'original_group_id', 'original_transaction_sequence', 'original_transaction_type'] as $key) {
            if (trim($ack[$key]) === '') {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new AckParseException('ACK_MISSING_CORRELATION', 'ACK correlation keys are required.', ['line' => $lineNumber, 'missing' => $missing]);
        }

        $originalType = strtoupper(trim($ack['original_transaction_type']));
        $transactionType = $currentAck['transaction']['record_type'];
        if (!in_array($originalType, ['HDR', 'TRL'], true) && $originalType !== $transactionType) {
            throw new AckParseException('ACK_TRANSACTION_TYPE_MISMATCH', 'Original transaction type does not match ACK transaction.', ['line' => $lineNumber, 'expected' => $originalType, 'actual' => $transactionType]);
        }

        $creationTitle = $this->normalizeValue($ack['creation_title']);
        $workTitle = $this->normalizeValue($currentAck['transaction']['work_title']);
        if ($creationTitle !== '' && $workTitle !== '' && $creationTitle !== $workTitle) {
            throw new AckParseException('ACK_CREATION_TITLE_MISMATCH', 'ACK creation title must match transaction title.', ['line' => $lineNumber]);
        }

        $submitterCreation = $this->normalizeValue($ack['submitter_creation_number']);
        $submitterWorkNumber = $this->normalizeValue($currentAck['transaction']['submitter_work_number']);
        if ($submitterCreation !== '' && $submitterWorkNumber !== '' && $submitterCreation !== $submitterWorkNumber) {
            throw new AckParseException('ACK_SUBMITTER_CREATION_MISMATCH', 'ACK submitter creation number must match transaction submitter work number.', ['line' => $lineNumber]);
        }

        $currentGroup['acknowledgements'][] = $this->buildAcknowledgement($currentAck, $includePayload);
        $currentAck = null;
    }

    private function buildAcknowledgement(array $currentAck, bool $includePayload): array
    {
        $ack = $currentAck['ack'];
        $transaction = $currentAck['transaction'];

        $acknowledgement = [
            'correlation' => [
                'creation_date' => $this->cleanValue($ack['creation_date']),
                'creation_time' => $this->cleanValue($ack['creation_time']),
                'original_group_id' => $this->cleanValue($ack['original_group_id']),
                'original_transaction_sequence' => $this->cleanValue($ack['original_transaction_sequence']),
                'original_transaction_type' => $this->cleanValue($ack['original_transaction_type']),
            ],
            'work' => [
                'submitter_creation_number' => $this->cleanValue($ack['submitter_creation_number']),
                'recipient_creation_number' => $this->cleanValue($ack['recipient_creation_number']),
                'creation_title' => $this->cleanValue($ack['creation_title']),
                'transaction_type' => $transaction['record_type'],
                'submitter_work_number' => $this->cleanValue($transaction['submitter_work_number'] ?? ''),
                'iswc' => $this->cleanValue($transaction['iswc'] ?? ''),
            ],
            'status' => [
                'transaction_status' => $this->cleanValue($ack['transaction_status']),
                'processing_date' => $this->cleanValue($ack['processing_date']),
            ],
            'messages' => $currentAck['messages'],
        ];

        if ($includePayload) {
            $acknowledgement['payload'] = [
                'ack' => $currentAck['raw']['ack'],
                'messages' => $currentAck['raw']['messages'],
                'transaction' => $currentAck['raw']['transaction'],
                'exception' => $currentAck['raw']['exception'],
            ];
        }

        return $acknowledgement;
    }

    private function cleanValue(?string $value): string
    {
        return trim((string) $value);
    }

    private function normalizeValue(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }

    private function resolveVersion(string $headerLine, array $header): string
    {
        if ($this->forcedVersion !== null) {
            return $this->forcedVersion;
        }

        $version = $header['version'] ?? '';
        if ($version !== '') {
            return $version;
        }

        return strlen($headerLine) >= 167 ? '2.2' : '2.1';
    }

    private function resolveRecordClasses(string $version): array
    {
        if ($version === '2.2') {
            return [V22AckRecord::class, V22MsgRecord::class, V22NwrRecord::class, V22RevRecord::class];
        }

        return [V21AckRecord::class, V21MsgRecord::class, V21NwrRecord::class, V21RevRecord::class];
    }

    private function parseFilenameCodes(?string $filename): array
    {
        if ($filename === null || $filename === '') {
            return [null, null];
        }

        $base = basename($filename);
        if (!preg_match('/^CW\d{2}\d{4}([A-Z0-9]{2,3})_([A-Z0-9]{2,3})\.V\d{2}$/i', $base, $matches)) {
            return [null, null];
        }

        return [strtoupper($matches[1]), strtoupper($matches[2])];
    }

    private function isDetailRecord(string $recordType): bool
    {
        return in_array($recordType, self::DETAIL_RECORD_TYPES, true);
    }
}
