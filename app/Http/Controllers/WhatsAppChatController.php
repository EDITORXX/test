<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppApiService;
use App\Services\WhatsAppConversationScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WhatsAppChatController extends Controller
{
    protected $whatsappService;
    protected $scopeService;

    public function __construct(
        WhatsAppApiService $whatsappService,
        WhatsAppConversationScopeService $scopeService
    )
    {
        $this->whatsappService = $whatsappService;
        $this->scopeService = $scopeService;
    }

    private function normalizeDirection(?string $direction): string
    {
        $value = strtolower(trim((string) $direction));

        return match ($value) {
            'sent', 'send', 'outgoing', 'from_me' => 'sent',
            default => 'received',
        };
    }

    private function formatMessage(WhatsAppMessage $message): array
    {
        return [
            'id' => $message->id,
            'direction' => $this->normalizeDirection($message->direction),
            'message' => $message->message,
            'status' => $message->status,
            'template_id' => $message->template_id,
            'created_at' => $message->created_at->format('Y-m-d H:i:s'),
            'sent_at' => $message->sent_at ? $message->sent_at->format('Y-m-d H:i:s') : null,
        ];
    }

    private function templateSendSucceeded(array $result): bool
    {
        if (!($result['success'] ?? false)) {
            return false;
        }

        $status = strtolower((string) data_get($result, 'data.status', ''));
        if ($status === 'error' || $status === 'failed' || $status === 'failure') {
            return false;
        }

        return true;
    }

    private function templateSendError(array $result): string
    {
        return data_get($result, 'data.message')
            ?: data_get($result, 'error')
            ?: 'Failed to send template message';
    }

    private function extractMessagesFromConversationPayload(array $result, WhatsAppConversation $conversation): array
    {
        $targetPhone = preg_replace('/[^0-9]/', '', $conversation->phone_number);
        $variants = array_values(array_unique(array_filter([
            $targetPhone,
            str_starts_with($targetPhone, '91') && strlen($targetPhone) === 12 ? substr($targetPhone, 2) : null,
            strlen($targetPhone) === 10 ? '91' . $targetPhone : null,
        ])));

        $payload = $result['data']['conversations'] ?? $result['data'] ?? [];
        if (!is_array($payload)) {
            return [];
        }

        foreach ($payload as $conversationPayload) {
            $candidatePhone = preg_replace('/[^0-9]/', '', (string) ($conversationPayload['phone'] ?? ''));

            if ($candidatePhone === '' || !in_array($candidatePhone, $variants, true)) {
                continue;
            }

            $messages = $conversationPayload['messages'] ?? [];
            return is_array($messages) ? $messages : [];
        }

        return [];
    }

    /**
     * Display chat interface
     */
    public function index()
    {
        $user = Auth::user();

        $conversations = $this->scopeService->conversationsFor($user)
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }, 'lead', 'user'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Auto-link conversations to leads if phone matches
        foreach ($conversations as $conversation) {
            if (!$conversation->lead_id) {
                $this->autoLinkToLead($conversation);
            }
        }

        return view('chat.index', compact('conversations'));
    }

    /**
     * Auto-link conversation to lead if phone number matches
     */
    private function autoLinkToLead(WhatsAppConversation $conversation)
    {
        // Format phone for matching (remove country code and spaces)
        $phone = preg_replace('/[^0-9]/', '', $conversation->phone_number);
        
        // Try to find lead by phone number
        $lead = \App\Models\Lead::where(function($query) use ($phone) {
            // Match with country code
            $query->whereRaw('REPLACE(REPLACE(phone, "+", ""), " ", "") LIKE ?', ['%' . $phone . '%'])
                  ->orWhereRaw('REPLACE(REPLACE(phone, "+", ""), " ", "") LIKE ?', ['%' . substr($phone, -10) . '%']); // Last 10 digits
        })->first();

        if ($lead) {
            $conversation->update(['lead_id' => $lead->id]);
            if (!$conversation->contact_name && $lead->name) {
                $conversation->update(['contact_name' => $lead->name]);
            }
        }
    }

    /**
     * Get all conversations for the authenticated user
     */
    public function getConversations(Request $request)
    {
        $user = Auth::user();

        $conversations = $this->scopeService->conversationsFor($user)
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }, 'lead', 'user'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($conversation) {
                $latestMessage = $conversation->getLatestMessage();
                
                // Auto-link to lead if not linked
                if (!$conversation->lead_id) {
                    $this->autoLinkToLead($conversation);
                    $conversation->refresh();
                }
                
                return [
                    'id' => $conversation->id,
                    'phone_number' => $conversation->phone_number,
                    'contact_name' => $conversation->contact_name,
                    'user_id' => $conversation->user_id,
                    'user_name' => $conversation->user ? $conversation->user->name : null,
                    'lead_id' => $conversation->lead_id,
                    'lead' => $conversation->lead ? [
                        'id' => $conversation->lead->id,
                        'name' => $conversation->lead->name,
                        'email' => $conversation->lead->email,
                        'status' => $conversation->lead->status,
                        'url' => route('leads.show', $conversation->lead->id),
                    ] : null,
                    'unread_count' => $conversation->getUnreadCount(),
                    'latest_message' => $latestMessage ? [
                        'message' => $latestMessage->message,
                        'direction' => $latestMessage->direction,
                        'created_at' => $latestMessage->created_at->format('Y-m-d H:i:s'),
                    ] : null,
                    'updated_at' => $conversation->updated_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    /**
     * Create new conversation (add number)
     */
    public function getLeads(Request $request)
    {
        $user = Auth::user();
        $query = $this->scopeService->visibleLeadsFor($user)
            ->select('id', 'name', 'phone', 'status');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                   ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $leads = $query->latest()->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $leads,
        ]);
    }

    public function createConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^[0-9+\-\s()]+$/',
            'contact_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Format phone number
        $phone = preg_replace('/[^0-9]/', '', $request->phone_number);
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        // Check if conversation already exists
        $conversation = WhatsAppConversation::where('user_id', Auth::id())
            ->where('phone_number', $phone)
            ->first();

        if ($conversation) {
            return response()->json([
                'success' => true,
                'message' => 'Conversation already exists',
                'data' => [
                    'id' => $conversation->id,
                    'phone_number' => $conversation->phone_number,
                    'contact_name' => $conversation->contact_name,
                ],
            ]);
        }

        // Create new conversation
        $conversation = WhatsAppConversation::create([
            'user_id' => Auth::id(),
            'phone_number' => $phone,
            'contact_name' => $request->contact_name,
        ]);

        // Auto-link to lead if phone matches
        $this->autoLinkToLead($conversation);
        $conversation->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Conversation created successfully',
            'data' => [
                'id' => $conversation->id,
                'phone_number' => $conversation->phone_number,
                'contact_name' => $conversation->contact_name,
                'lead_id' => $conversation->lead_id,
                'lead' => $conversation->lead ? [
                    'id' => $conversation->lead->id,
                    'name' => $conversation->lead->name,
                    'status' => $conversation->lead->status,
                ] : null,
            ],
        ]);
    }

    /**
     * Get conversation with messages
     */
    private function syncMessagesFromAPI(\App\Models\WhatsAppConversation $conversation): void
    {
        try {
            $phone = preg_replace('/[^0-9]/', '', $conversation->phone_number);
            $localPhone = str_starts_with($phone, '91') && strlen($phone) === 12
                ? substr($phone, 2)
                : $phone;

            $result = $this->whatsappService->getMessages($localPhone);
            $messages = ($result['success'] ?? false) && is_array($result['data'] ?? null)
                ? $result['data']
                : [];

            if (empty($messages)) {
                $conversationResult = $this->whatsappService->getConversations($phone);
                $messages = $this->extractMessagesFromConversationPayload($conversationResult, $conversation);
            }

            if (!is_array($messages) || empty($messages)) {
                return;
            }

            foreach ($messages as $msg) {
                $messageText = $msg['message']
                    ?? $msg['body']
                    ?? $msg['text']
                    ?? $msg['value']
                    ?? $msg['original_message']
                    ?? null;
                $direction = $this->normalizeDirection(
                    $msg['direction']
                    ?? (($msg['from_me'] ?? false) ? 'outgoing' : null)
                    ?? (($msg['is_message_by_contact'] ?? false) ? 'incoming' : 'outgoing')
                );
                $externalId = $msg['fb_message_id'] ?? $msg['id'] ?? $msg['message_id'] ?? null;
                $sentAt = $msg['created_at'] ?? $msg['timestamp'] ?? $msg['reply_at'] ?? null;

                if (!$messageText || !$externalId) continue;

                \App\Models\WhatsAppMessage::updateOrCreate(
                    ['message_id' => (string)$externalId, 'conversation_id' => $conversation->id],
                    [
                        'direction' => $direction,
                        'message' => $messageText,
                        'status' => $msg['status'] ?? 'delivered',
                        'sent_at' => $sentAt ? \Carbon\Carbon::parse($sentAt) : now(),
                    ]
                );
            }

            $conversation->touch();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('WhatsApp sync failed: ' . $e->getMessage());
        }
    }

    public function getConversation($id)
    {
        $user = Auth::user();

        $conversation = $this->scopeService->conversationsFor($user)
            ->with(['messages', 'user', 'lead'])
            ->where('id', $id)
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found or unauthorized',
            ], 404);
        }

        // Auto-link to lead if not linked
        if (!$conversation->lead_id) {
            $this->autoLinkToLead($conversation);
            $conversation->refresh();
        }

        // Mark as read
        $conversation->markAsRead();

        $this->syncMessagesFromAPI($conversation);

        $conversation->load('messages', 'user', 'lead');
        $messages = $conversation->messages->map(fn ($message) => $this->formatMessage($message));

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => [
                    'id' => $conversation->id,
                    'phone_number' => $conversation->phone_number,
                    'contact_name' => $conversation->contact_name,
                    'user_id' => $conversation->user_id,
                    'user_name' => $conversation->user ? $conversation->user->name : null,
                    'lead_id' => $conversation->lead_id,
                    'lead' => $conversation->lead ? [
                        'id' => $conversation->lead->id,
                        'name' => $conversation->lead->name,
                        'email' => $conversation->lead->email,
                        'status' => $conversation->lead->status,
                        'url' => route('leads.show', $conversation->lead->id),
                    ] : null,
                ],
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Send message
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:whatsapp_conversations,id',
            'message' => 'required|string|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation = $this->scopeService->resolveConversation(Auth::user(), $request->conversation_id);

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        try {
            // Send message via API
            $result = $this->whatsappService->sendTextMessage(
                $conversation->phone_number,
                $request->message
            );

            // Map API status to database status
            $dbStatus = 'sent'; // Default
            if ($result['success']) {
                $apiStatus = $result['data']['status'] ?? null;
                // Map API status values to database enum values
                if ($apiStatus === 'success' || $apiStatus === 'sent' || $apiStatus === 'delivered' || $apiStatus === 'read') {
                    $dbStatus = $apiStatus === 'success' ? 'sent' : $apiStatus;
                } else {
                    $dbStatus = 'sent'; // Default to sent if status is unknown
                }
            } else {
                $dbStatus = 'failed';
            }

            // Save message to database
            $message = WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
                'direction' => 'sent',
                'message' => $request->message,
                'message_id' => $result['success'] ? ($result['data']['id'] ?? $result['data']['message_id'] ?? null) : null,
                'status' => $dbStatus,
                'error_message' => $result['success'] ? null : ($result['error'] ?? 'Failed to send message'),
                'api_response' => $result,
                'sent_at' => $result['success'] ? now() : null,
            ]);

            // Update conversation timestamp
            $conversation->touch();

            if (Auth::user()->isAssistantSalesManager() || Auth::user()->isSeniorManager()) {
                Log::info('Scoped WhatsApp message sent', [
                    'sender_user_id' => Auth::id(),
                    'sender_role' => Auth::user()->role?->slug,
                    'conversation_id' => $conversation->id,
                    'lead_id' => $conversation->lead_id,
                    'message_type' => 'text',
                    'sent_at' => now()->toDateTimeString(),
                ]);
            }

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => [
                        'id' => $message->id,
                        'direction' => $message->direction,
                        'message' => $message->message,
                        'status' => $message->status,
                        'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send message',
                    'error' => $result['error'] ?? 'Unknown error',
                    'data' => [
                        'id' => $message->id,
                        'status' => $message->status,
                    ],
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Send Message Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send template message
     */
    public function sendTemplateMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:whatsapp_conversations,id',
            'template_id' => 'required|string',
            'parameters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $conversation = $this->scopeService->resolveConversation($user, $request->conversation_id);

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        try {
            // Get template content
            $template = WhatsAppTemplate::where('template_id', $request->template_id)->first();
            $messageContent = $template ? $template->content : 'Template message';

            // Send template message via API
            $result = $this->whatsappService->sendTemplateMessage(
                $conversation->phone_number,
                $template?->name ?: $request->template_id,
                $request->parameters ?? [],
                $template?->language
            );

            // Map API status to database status
            $sendSucceeded = $this->templateSendSucceeded($result);
            $dbStatus = 'sent'; // Default
            if ($sendSucceeded) {
                $apiStatus = $result['data']['status'] ?? null;
                // Map API status values to database enum values
                if ($apiStatus === 'success' || $apiStatus === 'sent' || $apiStatus === 'delivered' || $apiStatus === 'read') {
                    $dbStatus = $apiStatus === 'success' ? 'sent' : $apiStatus;
                } else {
                    $dbStatus = 'sent'; // Default to sent if status is unknown
                }
            } else {
                $dbStatus = 'failed';
            }

            // Save message to database
            $message = WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
                'direction' => 'sent',
                'message' => $messageContent ?: ($template?->name ?: 'Template message'),
                'message_id' => $sendSucceeded ? ($result['data']['id'] ?? $result['data']['message_id'] ?? null) : null,
                'template_id' => $request->template_id,
                'status' => $dbStatus,
                'error_message' => $sendSucceeded ? null : $this->templateSendError($result),
                'api_response' => $result,
                'sent_at' => $sendSucceeded ? now() : null,
            ]);

            // Update conversation timestamp
            $conversation->touch();

            if ($user->isAssistantSalesManager() || $user->isSeniorManager()) {
                Log::info('Scoped WhatsApp template sent', [
                    'sender_user_id' => $user->id,
                    'sender_role' => $user->role?->slug,
                    'conversation_id' => $conversation->id,
                    'lead_id' => $conversation->lead_id,
                    'message_type' => 'template',
                    'template_id' => $request->template_id,
                    'sent_at' => now()->toDateTimeString(),
                ]);
            }

            if ($sendSucceeded) {
                return response()->json([
                    'success' => true,
                    'message' => 'Template message sent successfully',
                    'data' => [
                        'id' => $message->id,
                        'direction' => $message->direction,
                        'message' => $message->message,
                        'template_id' => $message->template_id,
                        'status' => $message->status,
                        'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send template message',
                    'error' => $this->templateSendError($result),
                    'data' => [
                        'id' => $message->id,
                        'status' => $message->status,
                    ],
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Send Template Message Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending template message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available templates
     */
    public function getTemplates()
    {
        // First try to get from database
        $templates = WhatsAppTemplate::getAvailableTemplates();

        // If no templates in database or preview content is blank, refresh from API
        if ($templates->isEmpty() || $templates->contains(fn ($template) => blank($template->content))) {
            $apiResult = $this->whatsappService->getTemplates();
            if ($apiResult['success'] && !empty($apiResult['data'])) {
                WhatsAppTemplate::syncFromAPI($apiResult['data']);
                $templates = WhatsAppTemplate::getAvailableTemplates();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $templates->map(function($template) {
                return [
                    'id' => $template->id,
                    'template_id' => $template->template_id,
                    'name' => $template->name,
                    'content' => $template->content,
                    'category' => $template->category,
                    'language' => $template->language,
                    'template_name' => $template->name,
                ];
            }),
        ]);
    }

    public function syncMessages($id)
    {
        $user = Auth::user();

        $conversation = $this->scopeService->resolveConversation($user, $id);

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        $this->syncMessagesFromAPI($conversation);
        $conversation->load('messages');

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $conversation->messages
                    ->sortBy('created_at')
                    ->values()
                    ->map(fn ($message) => $this->formatMessage($message)),
            ],
        ]);
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();

        $conversation = $this->scopeService->resolveConversation($user, $id);

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        $conversation->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Conversation marked as read',
        ]);
    }

    /**
     * Delete conversation
     */
    public function deleteConversation($id)
    {
        $user = Auth::user();

        $conversation = $this->scopeService->resolveConversation($user, $id);

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully',
        ]);
    }

    /**
     * Sync templates from API
     */
    public function syncTemplates()
    {
        try {
            // Check if API is configured
            if (!$this->whatsappService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp API is not configured. Please configure API settings first.',
                    'error' => 'API not configured',
                ], 400);
            }
            
            $apiResult = $this->whatsappService->getTemplates();
            
            if (!$apiResult['success']) {
                $errorMessage = $apiResult['error'] ?? 'Failed to fetch templates from API';
                
                // Provide more helpful error messages
                if (str_contains($errorMessage, 'Could not resolve host') || str_contains($errorMessage, 'cURL error 6')) {
                    $errorMessage = 'Cannot connect to API server. Please check the Base URL in settings. Current URL: ' . ($this->whatsappService->settings->base_url ?? 'Not set');
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => $apiResult['error'] ?? 'Unknown error',
                    'details' => $apiResult,
                ], 500);
            }
            
            $templates = [];
            if (isset($apiResult['data'])) {
                $data = $apiResult['data'];
                // Handle different response formats
                if (isset($data['templates']) && is_array($data['templates'])) {
                    $templates = $data['templates'];
                } elseif (isset($data['data']) && is_array($data['data'])) {
                    $templates = $data['data'];
                } elseif (is_array($data)) {
                    $templates = $data;
                }
            }
            
            if (empty($templates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No templates found in API response',
                    'api_response' => $apiResult,
                ], 404);
            }
            
            $syncedCount = 0;
            $errors = [];
            
            foreach ($templates as $template) {
                try {
                    // Handle different template ID formats
                    $templateId = $template['id'] ?? $template['template_id'] ?? $template['name'] ?? null;
                    
                    if (!$templateId) {
                        $errors[] = 'Template missing ID: ' . json_encode($template);
                        continue;
                    }
                    
                    WhatsAppTemplate::updateOrCreate(
                        ['template_id' => $templateId],
                        [
                            'name' => $template['name'] ?? $template['template_name'] ?? 'Untitled Template',
                            'content' => WhatsAppTemplate::extractContent($template),
                            'category' => $template['category'] ?? $template['type'] ?? null,
                            'language' => $template['language'] ?? data_get($template, 'language.code') ?? $template['lang'] ?? 'en',
                            'is_active' => ($template['status'] ?? $template['state'] ?? 'APPROVED') === 'APPROVED',
                        ]
                    );
                    $syncedCount++;
                } catch (\Exception $e) {
                    Log::error('Error syncing template: ' . $e->getMessage(), ['template' => $template]);
                    $errors[] = 'Template sync error: ' . $e->getMessage();
                    continue;
                }
            }
            
            $response = [
                'success' => true,
                'message' => "Successfully synced {$syncedCount} template(s) from API",
                'synced_count' => $syncedCount,
                'total_templates' => WhatsAppTemplate::count(),
            ];
            
            if (!empty($errors)) {
                $response['warnings'] = $errors;
            }
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('WhatsApp Sync Templates Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error syncing templates: ' . $e->getMessage(),
            ], 500);
        }
    }
}
