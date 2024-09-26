<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\PrintReceiptRequest;
use App\Http\Requests\Transaction\SendReceiptRequest;
use App\Http\Requests\Transaction\UpdateRequest;
use App\Http\Resources\DataCollectionResource;
use App\Mail\TransactionEmailReceiptMail;
use App\Mail\TransactionReceiptMail;
use App\Models\DataCollection;
use App\Models\MailTemplate;
use App\Modules\PrintNode\src\Models\PrintJob;
use App\Modules\PrintNode\src\Resources\PrintJobResource;
use App\Services\PdfService;
use Illuminate\Support\Facades\Mail;

class TransactionController extends Controller
{
    public function update(UpdateRequest $request, int $transactionId): DataCollectionResource
    {
        $transaction = DataCollection::findOrFail($transactionId);

        $transaction->update($request->validated());

        return DataCollectionResource::make($transaction);
    }

    public function sendReceipt(SendReceiptRequest $request): bool
    {
        $transaction = DataCollection::findOrFail($request->validated('id'));

        /** @var MailTemplate $template */
        $template = MailTemplate::query()
            ->where('code', 'transaction_email_receipt')
            ->where('mailable', TransactionEmailReceiptMail::class)
            ->first();

        $products = $this->getProducts($transaction);

        $email = new TransactionEmailReceiptMail($template, [
            'transaction' => [
                'id' => $transaction->id,
                'subtotal' => $transaction->total_sold_price,
                'total' => $transaction->total_sold_price,
                'shipping' => 0,
                'tax' => 0,
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
            ],
            'shipping_address' => $transaction->shippingAddress->toArray(),
            'billing_address' => $transaction->billingAddress->toArray(),
            'products' => $products,
        ]);

        Mail::to($transaction->shippingAddress->email)->send($email);

        return true;
    }

    public function printReceipt(PrintReceiptRequest $request): PrintJobResource
    {
        $transaction = DataCollection::findOrFail($request->validated('id'));

        /** @var MailTemplate $template */
        $template = MailTemplate::query()
            ->where('code', 'transaction_receipt')
            ->where('mailable', TransactionReceiptMail::class)
            ->first();

        $products = $this->getProducts($transaction);

        $html = PdfService::fromMustacheTemplate(
            $template->text_template,
            [
                'transaction' => [
                    'id' => $transaction->id,
                    'discount' => $transaction->total_discount,
                    'total' => $transaction->total_sold_price,
                    'shipping' => 0,
                    'tax' => 0,
                    'seller' => 'ShipTown',
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                ],
                'products' => $products,
            ],
            true
        );

        $template = $this->parseReceiptTemplate($html);

        $printJob = new PrintJob;
        $printJob->printer_id = $request->printer_id;
        $printJob->title = "transaction $transaction->id receipt";
        $printJob->content = base64_encode($template);
        $printJob->content_type = 'raw_base64';
        $printJob->save();

        return PrintJobResource::make($printJob);
    }

    private function getProducts(DataCollection $transaction): array
    {
        return $transaction->records->map(function ($record) {
            $product = $record->product;

            return [
                'sku' => $product->sku,
                'name' => $product->name,
                'quantity' => $record->quantity_scanned,
                'price' => $record->unit_sold_price,
            ];
        })->toArray();
    }

    private function parseReceiptTemplate(string $template): string
    {
        $esc = chr(27);

        $codes = [
            'left' => $esc.'a'.chr(0),
            'center' => $esc.'a'.chr(1),
            'right' => $esc.'a'.chr(2),
            'font-large' => chr(29).'!'.chr(68),
            'font-big' => chr(29).'!'.chr(34),
            'font-normal' => chr(29).'!'.chr(0),
            'br' => chr(10),
            'cut' => chr(29).'V'.chr(1),
            'bold' => $esc.'E'.chr(1),
            'bold-off' => $esc.'E'.chr(0),
            'tab' => chr(9),
            'dashed-line' => $esc.'a'.chr(1).'-------------------------'.$esc.'a'.chr(0),
        ];

        $tagsWithCodes = [
            'left' => $codes['left'],
            'center' => $codes['center'],
            'right' => $codes['right'],
            'bold' => $codes['bold'],
            'bold-off' => $codes['bold-off'],
            'br' => $codes['br'],
            'cut' => $codes['cut'],
            'font-large' => $codes['font-large'],
            'font-big' => $codes['font-big'],
            'font-normal' => $codes['font-normal'],
            'tab' => $codes['tab'],
            'dashed-line' => $codes['dashed-line'],
        ];

        $tagsWithNonEmptyEnd = [
            'center' => $codes['left'],
            'right' => $codes['left'],
            'bold' => $codes['bold-off'],
            'font-large' => $codes['font-normal'],
            'font-big' => $codes['font-normal'],
        ];

        foreach ($tagsWithCodes as $tag => $code) {
            $template = str_replace("<esc-$tag>", $code, $template);
            if (isset($tagsWithNonEmptyEnd[$tag])) {
                $template = str_replace("</esc-$tag>", $tagsWithNonEmptyEnd[$tag], $template);
            } else {
                $template = str_replace("</esc-$tag>", '', $template);
            }
        }

        while (str_contains($template, '<esc-table>')) {
            $tableStart = strpos($template, '<esc-table>');
            $tableEnd = strpos($template, '</esc-table>', $tableStart);
            $tableContent = substr($template, $tableStart, $tableEnd - $tableStart + 12);

            $columns = [];
            preg_match_all('/<esc-column[^>]*>(.*?)<\/esc-column>/', $tableContent, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $columns[] = [
                    'content' => $match[1],
                    'attributes' => $this->parseAttributes($match[0]),
                ];
            }

            $parsedTable = $this->columnify($columns);
            $template = str_replace($tableContent, $parsedTable, $template);
        }

        $parsedTemplate = $esc.'@';
        $parsedTemplate .= $template;
        $parsedTemplate .= $esc.'d'.chr(5);
        $parsedTemplate .= $esc.'i';
        $pinValue = 0 + 48; // Character '0' or '1'.
        $onValue = intdiv(120, 2);
        $offValue = intdiv(240, 2);
        $parsedTemplate .= $esc.'p'.chr($pinValue).chr($onValue).chr($offValue);

        ray($parsedTemplate);

        return $parsedTemplate;
    }

    private function parseAttributes(string $tag): array
    {
        $attributes = [];
        preg_match_all('/(\w+)="([^"]*)"/', $tag, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[2];
        }

        return $attributes;
    }

    private function columnify(array $columns, int $space = 2): string
    {
        $wrapped = [];
        $lines = [];
        $totalColumns = count($columns);

        foreach ($columns as $index => $column) {
            $colWidth = $column['attributes']['width'];
            if ($index === 0 || $index === $totalColumns - 1) {
                $colWidth -= $space / 2;
            } else {
                $colWidth -= $space;
            }
            $wrapped[$index] = wordwrap($column['content'], $colWidth, "\n", true);
            $lines[$index] = explode("\n", $wrapped[$index]);
        }

        $maxLines = max(array_map('count', $lines));
        $allLines = [];

        for ($i = 0; $i < $maxLines; $i++) {
            $line = '';
            foreach ($columns as $index => $column) {
                $colWidth = $column['attributes']['width'];
                if ($index === 0 || $index === $totalColumns - 1) {
                    $colWidth -= $space / 2;
                } else {
                    $colWidth -= $space;
                }
                $text = $lines[$index][$i] ?? '';
                if (isset($column['attributes']['align']) && $column['attributes']['align'] === 'right') {
                    $line .= str_pad($text, $colWidth, ' ', STR_PAD_LEFT);
                } else {
                    $line .= str_pad($text, $colWidth);
                }
                if ($index < $totalColumns - 1) {
                    $line .= str_repeat(' ', $space);
                }
            }
            $allLines[] = $line;
        }

        return implode("\n", $allLines)."\n";
    }
}
