<?php

namespace App\Modules\Automations\src\Models;

use App\BaseModel;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property int priority
 * @property bool enabled
 * @property string name
 * @property mixed conditions
 * @property mixed actions
 */
class Automation extends BaseModel
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'modules_automations';

    /**
     * @var string[]
     */
    protected $fillable = [
        'priority',
        'enabled',
        'name',
        'description',
    ];

    protected $attributes = [
        'enabled' => false,
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public static function enabled(): Builder
    {
        return self::query()->where(['enabled' => true]);
    }

    public function allConditionsTrue(Order $order): bool
    {
        return $this->conditions
            ->every(function (Condition $condition) use ($order) {
                return $condition->isTrue($order);
            });
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(Condition::class)->orderBy('id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class)->orderBy('priority');
    }
}
