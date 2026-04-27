<?php

namespace App\Services;

use App\Models\FaqEntry;


class SupportBotService
{
    public function reply(string $message): array
    {
        $text = strtolower(trim($message));

        $faqEntries = FaqEntry::where('is_active', true)->get();

        foreach ($faqEntries as $faq) {
            $keywords = array_map('trim', explode(',', strtolower($faq->keywords)));

            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($text, $keyword)) {
                    return [
                        'matched' => true,
                        'reply' => $faq->answer,
                    ];
                }
            }
        }

        return [
            'matched' => false,
            'reply' => "I'm sorry, I couldn't find a clear answer to that.",
        ];
    }
}