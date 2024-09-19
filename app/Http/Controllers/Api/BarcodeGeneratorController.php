<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BarcodeGeneratorIndexRequest;
use Illuminate\Http\Response;
use Milon\Barcode\DNS1D;

class BarcodeGeneratorController extends Controller
{
    public function index(BarcodeGeneratorIndexRequest $request)
    {
        $content = $request->validated('content');
        $barcode_type = 'C39';
        $width = 0.60;
        $height = 15;
        $color = $request->validated('color', 'black');
        $show_code = false;

        $content_type = 'image/svg+xml';
        $filename = 'barcode.svg';

        $barcode = new DNS1D();
        return new Response(
            $barcode->getBarcodeSVG($content, $barcode_type, $width, $height, $color, $show_code),
            200,
            [
                'Content-Type' => $content_type,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]
        );
    }
}
