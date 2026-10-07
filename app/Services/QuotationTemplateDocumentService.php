<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationTemplate;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class QuotationTemplateDocumentService
{
    public const REQUIRED_PLACEHOLDERS = [
        'quotation_date',
        'client_name',
        'project_name',
        'project_location',
        'subject',
        'grand_total',
        'amount_in_words',
        'particular',
        'qty',
        'unit',
        'unit_cost',
        'line_total',
    ];

    public function validateTemplate(string $path): array
    {
        $processor = new TemplateProcessor($path);
        $variables = array_values(array_unique($processor->getVariables()));

        return array_values(array_diff(
            self::REQUIRED_PLACEHOLDERS,
            $variables
        ));
    }

    public function generate(
        Quotation $quotation,
        QuotationTemplate $template
    ): array {
        $quotation->loadMissing(['request', 'items']);

        $sourcePath = Storage::disk('local')->path($template->file_path);

        if (!is_file($sourcePath)) {
            throw new \RuntimeException('The selected quotation template file could not be found.');
        }

        $processor = new TemplateProcessor($sourcePath);

        $processor->setValue(
            'quotation_date',
            optional($quotation->created_at)->format('F d, Y') ?? now()->format('F d, Y')
        );
        $processor->setValue('quotation_no', $quotation->quotation_no ?? '');
        $processor->setValue('client_name', $quotation->request->full_name ?? 'Client');
        $processor->setValue('client_email', $quotation->request->email ?? '');
        $processor->setValue('client_phone', $quotation->request->phone ?? '');
        $processor->setValue('service_type', $quotation->request->service_type ?? '');
        $processor->setValue('project_name', $quotation->project_name ?? '');
        $processor->setValue('project_location', $quotation->project_location ?? '');
        $processor->setValue('subject', $quotation->subject ?? '');
        $processor->setValue('grand_total', number_format((float) $quotation->grand_total, 2));
        $processor->setValue(
            'amount_in_words',
            $this->amountToWords((float) $quotation->grand_total)
        );

        $items = $quotation->items->values();

        if ($items->isNotEmpty()) {
            $processor->cloneRow('particular', $items->count());

            foreach ($items as $index => $item) {
                $row = $index + 1;
                $lineTotal = round(
                    ((float) $item->quantity) * ((float) $item->unit_price),
                    2
                );

                $processor->setValue("item_no#{$row}", (string) $row);
                $processor->setValue("particular#{$row}", $item->description);
                $processor->setValue("qty#{$row}", $this->formatQuantity((float) $item->quantity));
                $processor->setValue("unit#{$row}", $item->unit);
                $processor->setValue("unit_cost#{$row}", number_format((float) $item->unit_price, 2));
                $processor->setValue("line_total#{$row}", number_format($lineTotal, 2));
            }
        }

        Storage::disk('local')->makeDirectory('generated-quotations');

        $filename = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '-',
            $quotation->quotation_no
        ) . '.docx';

        $relativePath = 'generated-quotations/' . $filename;
        $destinationPath = Storage::disk('local')->path($relativePath);

        $processor->saveAs($destinationPath);

        return [
            'path' => $relativePath,
            'name' => $filename,
        ];
    }

    protected function formatQuantity(float $value): string
    {
        if (floor($value) === $value) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    protected function amountToWords(float $amount): string
    {
        $whole = (int) floor($amount);
        $centavos = (int) round(($amount - $whole) * 100);

        $result = $this->integerToWords($whole) . ' PESOS';

        if ($centavos > 0) {
            $result .= ' AND ' .
                $this->integerToWords($centavos) .
                ' CENTAVOS';
        }

        return strtoupper($result);
    }

    protected function integerToWords(int $number): string
    {
        if ($number === 0) {
            return 'ZERO';
        }

        $ones = [
            0 => '',
            1 => 'ONE',
            2 => 'TWO',
            3 => 'THREE',
            4 => 'FOUR',
            5 => 'FIVE',
            6 => 'SIX',
            7 => 'SEVEN',
            8 => 'EIGHT',
            9 => 'NINE',
            10 => 'TEN',
            11 => 'ELEVEN',
            12 => 'TWELVE',
            13 => 'THIRTEEN',
            14 => 'FOURTEEN',
            15 => 'FIFTEEN',
            16 => 'SIXTEEN',
            17 => 'SEVENTEEN',
            18 => 'EIGHTEEN',
            19 => 'NINETEEN',
        ];

        $tens = [
            20 => 'TWENTY',
            30 => 'THIRTY',
            40 => 'FORTY',
            50 => 'FIFTY',
            60 => 'SIXTY',
            70 => 'SEVENTY',
            80 => 'EIGHTY',
            90 => 'NINETY',
        ];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            $base = intdiv($number, 10) * 10;
            $remainder = $number % 10;

            return trim(
                $tens[$base] .
                ($remainder ? ' ' . $ones[$remainder] : '')
            );
        }

        if ($number < 1000) {
            $hundreds = intdiv($number, 100);
            $remainder = $number % 100;

            return trim(
                $ones[$hundreds] . ' HUNDRED' .
                ($remainder
                    ? ' ' . $this->integerToWords($remainder)
                    : '')
            );
        }

        foreach ([
            1000000000 => 'BILLION',
            1000000 => 'MILLION',
            1000 => 'THOUSAND',
        ] as $scale => $label) {
            if ($number >= $scale) {
                $major = intdiv($number, $scale);
                $remainder = $number % $scale;

                return trim(
                    $this->integerToWords($major) .
                    ' ' . $label .
                    ($remainder
                        ? ' ' . $this->integerToWords($remainder)
                        : '')
                );
            }
        }

        return (string) $number;
    }
}
