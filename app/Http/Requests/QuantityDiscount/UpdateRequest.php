<?php

namespace App\Http\Requests\QuantityDiscount;

use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXForYPercentDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXForYPriceDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXGetYForZPercentDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXGetYForZPriceDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\VolumePurchasePercentDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\VolumePurchasePriceDiscount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:250',
            'configuration' => 'required|array',
            'job_class' => ['nullable', 'sometimes', 'string',
                Rule::in([
                    BuyXForYPercentDiscount::class,
                    BuyXForYPriceDiscount::class,
                    BuyXGetYForZPercentDiscount::class,
                    BuyXGetYForZPriceDiscount::class,
                    VolumePurchasePercentDiscount::class,
                    VolumePurchasePriceDiscount::class,
                ]),
            ],
        ];
    }
}
