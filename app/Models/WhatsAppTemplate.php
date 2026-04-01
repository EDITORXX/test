<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppTemplate extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'template_id',
        'name',
        'content',
        'category',
        'language',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function extractContent(array $template): string
    {
        $content = $template['content']
            ?? $template['body']
            ?? $template['message']
            ?? data_get($template, 'components.0.text')
            ?? null;

        if (is_string($content) && trim($content) !== '') {
            return trim($content);
        }

        $components = $template['components'] ?? [];
        if (is_string($components)) {
            $decoded = json_decode($components, true);
            $components = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($components)) {
            return '';
        }

        foreach ($components as $component) {
            $type = strtolower((string) ($component['type'] ?? ''));
            if ($type !== 'body') {
                continue;
            }

            $text = $component['text'] ?? data_get($component, 'example.body_text.0.0');
            if (is_string($text) && trim($text) !== '') {
                return trim($text);
            }
        }

        return '';
    }

    /**
     * Sync templates from API
     */
    public static function syncFromAPI(array $templates): void
    {
        foreach ($templates as $template) {
            self::updateOrCreate(
                ['template_id' => $template['id'] ?? $template['template_id']],
                [
                    'name' => $template['name'] ?? '',
                    'content' => self::extractContent($template),
                    'category' => $template['category'] ?? null,
                    'language' => $template['language'] ?? data_get($template, 'language.code') ?? 'en',
                    'is_active' => (($template['status'] ?? 'APPROVED') === 'APPROVED'),
                ]
            );
        }
    }

    /**
     * Get available active templates
     */
    public static function getAvailableTemplates()
    {
        return self::where('is_active', true)->orderBy('name')->get();
    }
}
