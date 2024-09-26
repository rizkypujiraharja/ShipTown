<?php

use App\Mail\TransactionReceiptMail;
use App\Models\MailTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        MailTemplate::query()->firstOrCreate(['code' => 'transaction_receipt'], [
            'mailable' => TransactionReceiptMail::class,
            'subject' => 'You just completed a #{{ variables.transaction.id }} transaction! Here is your receipt.',
            'html_template' => '',
            'text_template' => '
<esc-center>
<esc-font-big>Store Name</esc-font-big>
<esc-font-normal>
21 STONEWALL DRIVE
DUBLIN 24
D24 TF12
<esc-br></esc-br>
Seller: {{transaction.seller}}
Date: {{transaction.created_at}}
</esc-font-normal>
</esc-center>
<esc-dashed-line></esc-dashed-line>
<esc-left><esc-table><esc-column width="11">SKU</esc-column><esc-column width="25">Name</esc-column><esc-column width="6" align="right">Qty.</esc-column><esc-column width="6" align="right">Price</esc-column></esc-table>{{#products}}<esc-table><esc-column width="11">{{sku}}</esc-column><esc-column width="25">{{name}}</esc-column><esc-column width="6" align="right">{{quantity}}</esc-column><esc-column width="6" align="right">{{price}}</esc-column></esc-table>{{/products}}</esc-left>
<esc-dashed-line></esc-dashed-line>
<esc-left>
Discount: <esc-tab>${{transaction.discount}}</esc-tab>
Total: <esc-tab>${{transaction.total}}</esc-tab>
</esc-left>',
        ]);
    }
};
