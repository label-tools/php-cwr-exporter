<?php

use LabelTools\PhpCwrExporter\Acknowledgements\AckParseException;
use LabelTools\PhpCwrExporter\Acknowledgements\AckParser;
use LabelTools\PhpCwrExporter\Enums\MusicalWorkDistributionCategory;
use LabelTools\PhpCwrExporter\Enums\SenderType;
use LabelTools\PhpCwrExporter\Enums\TransactionType;
use LabelTools\PhpCwrExporter\Enums\VersionType;
use LabelTools\PhpCwrExporter\Records\Control\GrhRecord;
use LabelTools\PhpCwrExporter\Records\Control\GrtRecord;
use LabelTools\PhpCwrExporter\Records\Control\TrlRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\MsgRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\AckRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\NwrRecord;

function buildAckRecord(array $fields, int $transactionSequence = 0, int $recordSequence = 0): string
{
    return (new AckRecord(
        creationDate: $fields['creation_date'] ?? '',
        creationTime: $fields['creation_time'] ?? '',
        originalGroupId: (int) ($fields['original_group_id'] ?? 0),
        originalTransactionSequence: (int) ($fields['original_transaction_sequence'] ?? 0),
        originalTransactionType: $fields['original_transaction_type'] ?? '',
        creationTitle: $fields['creation_title'] ?? '',
        submitterCreationNumber: $fields['submitter_creation_number'] ?? '',
        recipientCreationNumber: $fields['recipient_creation_number'] ?? '',
        processingDate: $fields['processing_date'] ?? '',
        transactionStatus: $fields['transaction_status'] ?? '',
    ))->setRecordPrefix($transactionSequence, $recordSequence)
        ->toString();
}

function buildMsgRecord(array $fields, int $transactionSequence = 0, int $recordSequence = 1): string
{
    return (new MsgRecord(
        messageType: $fields['message_type'] ?? '',
        originalRecordSequence: (int) ($fields['original_record_sequence'] ?? 0),
        recordType: $fields['record_type'] ?? '',
        messageLevel: $fields['message_level'] ?? '',
        validationNumber: $fields['validation_number'] ?? '',
        messageText: $fields['message_text'] ?? '',
    ))->setRecordPrefix($transactionSequence, $recordSequence)
        ->toString();
}

function buildNwrRecord(string $workTitle, string $submitterWorkNumber, int $transactionSequence, int $recordSequence): string
{
    return (new NwrRecord(
        workTitle: $workTitle,
        submitterWorkNumber: $submitterWorkNumber,
        mwDistributionCategory: MusicalWorkDistributionCategory::POPULAR,
        versionType: VersionType::ORIGINAL_WORK,
    ))
        ->setRecordPrefix($transactionSequence, $recordSequence)
        ->toString();
}

function buildAckPayload(array $overrides = []): string
{
    $ackFields = array_replace([
        'creation_date' => '20240101',
        'creation_time' => '101500',
        'original_group_id' => 1,
        'original_transaction_sequence' => 0,
        'original_transaction_type' => 'NWR',
        'creation_title' => 'TEST WORK',
        'submitter_creation_number' => 'WORK0000000001',
        'recipient_creation_number' => 'SOC0000000001',
        'processing_date' => '20240102',
        'transaction_status' => 'AC',
    ], $overrides);

    $hdr = (new HdrRecord(
        senderType: SenderType::SOCIETY->value,
        senderId: '123456789',
        senderName: 'TEST SOCIETY',
        creationDate: '20240102',
        creationTime: '120000',
        transmissionDate: '20240102',
        characterSet: 'ASCII'
    ))->toString();

    $grh = (new GrhRecord(TransactionType::ACKNOWLEDGMENT->value, 1))->toString();

    $ack = buildAckRecord($ackFields, 0, 0);

    $msg = buildMsgRecord([
        'message_type' => 'T',
        'original_record_sequence' => 0,
        'record_type' => 'NWR',
        'message_level' => 'T',
        'validation_number' => '003',
        'message_text' => 'Transaction rejected due to test error.',
    ], 0, 1);

    $nwr = buildNwrRecord('TEST WORK', 'WORK0000000001', 0, 2);

    $grt = (new GrtRecord(1, 1, 3))->toString();
    $trl = (new TrlRecord(1, 1, 5))->toString();

    return implode("\r\n", [$hdr, $grh, $ack, $msg, $nwr, $grt, $trl]) . "\r\n";
}

describe('AckParser', function () {
    it('parses ACK files into a structured result', function () {
        $payload = buildAckPayload();
        $parser = AckParser::v21();

        $result = $parser->parse($payload, ['filename' => 'CW240001ABC_DEF.V21']);
        $data = $result->toArray();

        expect($data['file']['sender']['type'])->toBe('SO')
            ->and($data['file']['sender']['id'])->toBe('123456789')
            ->and($data['file']['sender']['name'])->toBe('TEST SOCIETY')
            ->and($data['file']['receiver']['code'])->toBe('DEF')
            ->and($data['file']['version'])->toBe('2.1')
            ->and($data['groups'][0]['group_id'])->toBe('00001')
            ->and($data['groups'][0]['acknowledgements'][0]['correlation']['original_transaction_type'])->toBe('NWR')
            ->and($data['groups'][0]['acknowledgements'][0]['work']['submitter_creation_number'])->toBe('WORK0000000001')
            ->and($data['groups'][0]['acknowledgements'][0]['status']['transaction_status'])->toBe('AC')
            ->and(count($data['groups'][0]['acknowledgements'][0]['messages']))->toBe(1);
    });

    it('rejects ACK records when record sequences do not continue', function () {
        $payload = buildAckPayload();
        $lines = explode("\r\n", trim($payload));
        $lines[3] = buildMsgRecord([
            'message_type' => 'T',
            'original_record_sequence' => 0,
            'record_type' => 'NWR',
            'message_level' => 'T',
            'validation_number' => '003',
            'message_text' => 'Transaction rejected due to test error.',
        ], 0, 9); // break continuation
        $payload = implode("\r\n", $lines) . "\r\n";

        $parser = AckParser::v21();

        try {
            $parser->parse($payload);
            $this->fail('Expected AckParseException not thrown.');
        } catch (AckParseException $exception) {
            expect($exception->getErrorCode())->toBe('ACK_RECORD_CONTINUATION');
        }
    });

    it('rejects ACK records when correlation keys are missing', function () {
        $payload = buildAckPayload();
        $lines = explode("\r\n", trim($payload));
        $ackLine = $lines[2];
        $lines[2] = substr($ackLine, 0, 19) . str_repeat(' ', 8) . substr($ackLine, 27);
        $payload = implode("\r\n", $lines) . "\r\n";

        $parser = AckParser::v21();

        try {
            $parser->parse($payload);
            $this->fail('Expected AckParseException not thrown.');
        } catch (AckParseException $exception) {
            expect($exception->getErrorCode())->toBe('ACK_MISSING_CORRELATION');
        }
    });

    it('parses the ASCAP sample ACK file with detail records', function () {
        $payload = file_get_contents(__DIR__ . '/../Fixtures/ascap_ack_sample.txt');
        $parser = AckParser::auto();

        $result = $parser->parse($payload);
        $data = $result->toArray();

        expect($data['file']['sender']['type'])->toBe('SO')
            ->and($data['file']['sender']['id'])->toBe('123456789')
            ->and($data['file']['sender']['name'])->toBe('SAMPLE SOCIETY')
            ->and($data['file']['version'])->toBe('2.1')
            ->and(count($data['groups']))->toBe(1)
            ->and($data['groups'][0]['group_id'])->toBe('00001')
            ->and(count($data['groups'][0]['acknowledgements']))->toBe(54);

        $firstAck = $data['groups'][0]['acknowledgements'][0];
        expect($firstAck['correlation']['creation_date'])->toBe('20251217')
            ->and($firstAck['correlation']['original_transaction_sequence'])->toBe('00000034')
            ->and($firstAck['work']['submitter_creation_number'])->toBe('W0000000000000')
            ->and($firstAck['status']['transaction_status'])->toBe('RA')
            ->and(count($firstAck['messages']))->toBe(0);

        $lastAck = $data['groups'][0]['acknowledgements'][53];
        expect($lastAck['work']['creation_title'])->toBe('WORK 00053')
            ->and($lastAck['status']['transaction_status'])->toBe('AC');
    });

    it('parses SACM ACK files that omit NWR/REV records', function () {
        $payload = file_get_contents(__DIR__ . '/../Fixtures/sacm_ack_sample.txt');
        $parser = AckParser::auto();

        $result = $parser->parse($payload);
        $data = $result->toArray();

        $acks = $data['groups'][0]['acknowledgements'];
        $firstAck = $acks[0] ?? null;
        $ackWithMessage = null;
        foreach ($acks as $ack) {
            if (!empty($ack['messages'])) {
                $ackWithMessage = $ack;
                break;
            }
        }

        expect($data['file']['sender']['name'])->not->toBe('')
            ->and(count($acks))->toBeGreaterThan(1)
            ->and($firstAck)->not->toBeNull()
            ->and(trim($firstAck['work']['creation_title']))->not->toBe('')
            ->and($ackWithMessage)->not->toBeNull()
            ->and($ackWithMessage['messages'][0]['message_type'])->toBe('T');
    });

    it('has payload when include_payload is true', function () {
        $payload = buildAckPayload();
        $parser = AckParser::v21();

        $result = $parser->parse($payload, ['include_payload' => true]);
        $data = $result->toArray();

        expect($data['groups'][0]['acknowledgements'][0])->toHaveKey('payload');
    });

    it('omits payload by default', function () {
        $payload = buildAckPayload();
        $parser = AckParser::v21();

        $result = $parser->parse($payload);
        $data = $result->toArray();

        expect($data['groups'][0]['acknowledgements'][0])->not->toHaveKey('payload');
    });
});
