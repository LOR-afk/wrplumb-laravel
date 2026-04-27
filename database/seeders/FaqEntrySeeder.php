<?php

namespace Database\Seeders;

use App\Models\FaqEntry;
use Illuminate\Database\Seeder;

class FaqEntrySeeder extends Seeder
{
    public function run(): void
    {
        FaqEntry::insert([
            [
                'question' => 'How can I request a quotation?',
                'answer' => 'You can request a quotation by filling out the free quotation form on the homepage. After submission, our team will review your request and assign the appropriate personnel.',
                'keywords' => 'quotation,quote,request quotation,free quotation,process,steps,how to request,how to get quotation',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'How do I track my request?',
                'answer' => 'You can track your request through the client dashboard under My Requests.',
                'keywords' => 'track,status,request,my request,quotation status,follow up',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'How do I contact support?',
                'answer' => 'You can chat here directly. If your concern needs human assistance, the system will automatically route it to HR or Admin.',
                'keywords' => 'support,help,contact support,customer service,chat with support',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}