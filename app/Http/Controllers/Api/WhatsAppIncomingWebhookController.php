<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppIncomingWebhookController extends Controller
{
    public function receive(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('WhatsApp incoming webhook payload received', $payload);

            $parsed = $this->parseIncomingPayload($payload);

            if (!$parsed['processable']) {
                Log::info('WhatsApp incoming webhook ignored', [
                    'reason' => $parsed['reason'],
                    'payload' => $payload,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $parsed['reason'],
                ], 200);
            }

            $phone = $this->normalizePhone($parsed['phone']);
            $contactName = $parsed['contact_name'];
            $leadId = $this->findLeadIdByPhone($phone);

            $conversation = WhatsAppConversation::where('phone_number', $phone)->first();
            if (!$conversation) {
                $conversation = WhatsAppConversation::create([
                    'user_id' => 1, // Existing safe default owner
                    'phone_number' => $phone,
                    'contact_name' => $contactName,
                    'lead_id' => $leadId,
                ]);

                Log::info('WhatsApp incoming webhook conversation created', [
                    'conversation_id' => $conversation->id,
                    'phone_number' => $phone,
                    'lead_id' => $leadId,
                ]);
            } else {
                $updates = [];

                if (!$conversation->contact_name && $contactName) {
                    $updates['contact_name'] = $contactName;
                }

                if (!$conversation->lead_id && $leadId) {
                    $updates['lead_id'] = $leadId;
                }

                if (!empty($updates)) {
                    $conversation->update($updates);
                }
            }

            $message = WhatsAppMessage::updateOrCreate(
                [
                    'message_id' => (string) $parsed['message_id'],
                    'conversation_id' => $conversation->id,
                ],
                [
                    'user_id' => $conversation->user_id,
                    'direction' => 'received',
                    'message' => $parsed['message'],
                    'status' => 'delivered',
                    'api_response' => [
                        'webhook_payload' => $payload,
                        'parsed_message' => $parsed['raw_message'],
                        'parsed_contact' => $parsed['raw_contact'],
                        'message_type' => $parsed['type'],
                    ],
                    'sent_at' => $parsed['sent_at'],
                ]
            );

            $conversation->touch();

            Log::info('WhatsApp incoming webhook message saved', [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'external_message_id' => $parsed['message_id'],
                'message_type' => $parsed['type'],
            ]);

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook parse failure', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            return response()->json(['success' => false], 200);
        }
    }

    private function parseIncomingPayload(array $payload): array
    {
        $value = data_get($payload, 'entry.0.changes.0.value');
        $field = data_get($payload, 'entry.0.changes.0.field');
        $contact = data_get($value, 'contacts.0', []);
        $message = data_get($value, 'messages.0', []);

        // Fallback to older flat structure if needed.
        if (!$value && empty($message)) {
            return $this->parseFlatPayload($payload);
        }

        if ($field !== 'messages') {
            return [
                'processable' => false,
                'reason' => 'Ignored webhook field',
            ];
        }

        if (empty($message)) {
            return [
                'processable' => false,
                'reason' => 'No incoming messages found',
            ];
        }

        $phone = $message['from'] ?? ($contact['wa_id'] ?? null);
        $messageId = $message['id'] ?? null;
        $type = $message['type'] ?? 'unknown';
        $messageText = $this->extractMessageText($message, $type);

        if (!$phone || !$messageId || !$messageText) {
            return [
                'processable' => false,
                'reason' => 'Missing phone, message id, or usable content',
            ];
        }

        return [
            'processable' => true,
            'phone' => $phone,
            'contact_name' => data_get($contact, 'profile.name'),
            'message_id' => $messageId,
            'type' => $type,
            'message' => $messageText,
            'sent_at' => $this->normalizeTimestamp($message['timestamp'] ?? null),
            'raw_message' => $message,
            'raw_contact' => $contact,
        ];
    }

    private function parseFlatPayload(array $payload): array
    {
        $phone = $payload['from'] ?? $payload['phone'] ?? $payload['sender'] ?? null;
        $message = $payload['message'] ?? $payload['body'] ?? $payload['text'] ?? null;
        $messageId = $payload['id'] ?? $payload['message_id'] ?? null;

        if (!$phone || !$message) {
            return [
                'processable' => false,
                'reason' => 'Missing phone or message',
            ];
        }

        return [
            'processable' => true,
            'phone' => $phone,
            'contact_name' => $payload['name'] ?? $payload['contact_name'] ?? null,
            'message_id' => (string) ($messageId ?? uniqid('flat_', true)),
            'type' => 'text',
            'message' => is_array($message) ? ($message['body'] ?? json_encode($message)) : $message,
            'sent_at' => $this->normalizeTimestamp($payload['timestamp'] ?? $payload['created_at'] ?? null),
            'raw_message' => $payload,
            'raw_contact' => [],
        ];
    }

    private function extractMessageText(array $message, string $type): ?string
    {
        return match ($type) {
            'text' => data_get($message, 'text.body'),
            'image' => '[Image]',
            'video' => '[Video]',
            'document' => '[Document: ' . (data_get($message, 'document.filename') ?: 'file') . ']',
            'location' => '[Location] ' . (data_get($message, 'location.address') ?: 'Shared location'),
            default => '[Unsupported message type: ' . $type . ']',
        };
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            return '91' . $phone;
        }

        return $phone;
    }

    private function normalizeTimestamp(mixed $timestamp): Carbon
    {
        if (is_numeric($timestamp)) {
            return Carbon::createFromTimestamp((int) $timestamp);
        }

        return $timestamp ? Carbon::parse($timestamp) : now();
    }

    private function findLeadIdByPhone(?string $phone): ?int
    {
        if (!$phone) {
            return null;
        }

        $lastTenDigits = strlen($phone) > 10 ? substr($phone, -10) : $phone;

        $lead = Lead::where(function ($query) use ($phone, $lastTenDigits) {
            $query->whereRaw('REPLACE(REPLACE(phone, "+", ""), " ", "") LIKE ?', ['%' . $phone . '%'])
                ->orWhereRaw('REPLACE(REPLACE(phone, "+", ""), " ", "") LIKE ?', ['%' . $lastTenDigits . '%']);
        })->first();

        return $lead?->id;
    }
}
