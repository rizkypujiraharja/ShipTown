<?php

namespace App\Models;

use App\BaseModel;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Module.
 *
 * @property int $id
 * @property string $service_provider_class
 * @property bool $enabled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Module newModelQuery()
 * @method static Builder|Module newQuery()
 * @method static Builder|Module query()
 * @method static Builder|Module whereCreatedAt($value)
 * @method static Builder|Module whereEnabled($value)
 * @method static Builder|Module whereId($value)
 * @method static Builder|Module whereServiceProviderClass($value)
 * @method static Builder|Module whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class Module extends BaseModel
{
    protected $fillable = [
        'service_provider_class',
        'enabled',
    ];

    protected $appends = [
        'enabled' => false,
        'settings_link' => '',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function getNameAttribute()
    {
        return $this->service_provider_class::$module_name;
    }

    public function getDescriptionAttribute()
    {
        return $this->service_provider_class::$module_description;
    }

    public function getSettingsLinkAttribute()
    {
        return $this->service_provider_class::$settings_link;
    }
}
