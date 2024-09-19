<?php

namespace App\Modules\StocktakeSuggestions\src\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Carbon|null $min_count_date
 */
class StocktakeSuggestionsConfiguration extends Model
{
    use HasFactory;

    protected $table = 'modules_stocktaking_suggestions_configurations';

    protected $fillable = [
        'min_count_date',
    ];

    protected function casts(): array
    {
        return [
            'min_count_date' => 'date',
        ];
    }
}
