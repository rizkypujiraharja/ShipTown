<?php

namespace App\Models;

use App\BaseModel;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\OrderAddress.
 *
 * @property int $id
 * @property string $company
 * @property string $gender
 * @property string $first_name
 * @property string $last_name
 * @property string $full_name
 * @property string $email
 * @property string $address1
 * @property string $address2
 * @property string $postcode
 * @property string $city
 * @property string $state_code
 * @property string $state_name
 * @property string $country_code
 * @property string $country_name
 * @property string $phone
 * @property string $fax
 * @property string $website
 * @property string $region
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order|null $order
 * @property bool encrypted
 *
 * @method static Builder|OrderAddress newModelQuery()
 * @method static Builder|OrderAddress newQuery()
 * @method static Builder|OrderAddress query()
 * @method static Builder|OrderAddress whereAddress1($value)
 * @method static Builder|OrderAddress whereAddress2($value)
 * @method static Builder|OrderAddress whereCity($value)
 * @method static Builder|OrderAddress whereCompany($value)
 * @method static Builder|OrderAddress whereCountryCode($value)
 * @method static Builder|OrderAddress whereCountryName($value)
 * @method static Builder|OrderAddress whereCreatedAt($value)
 * @method static Builder|OrderAddress whereDeletedAt($value)
 * @method static Builder|OrderAddress whereFax($value)
 * @method static Builder|OrderAddress whereFirstName($value)
 * @method static Builder|OrderAddress whereGender($value)
 * @method static Builder|OrderAddress whereId($value)
 * @method static Builder|OrderAddress whereLastName($value)
 * @method static Builder|OrderAddress wherePhone($value)
 * @method static Builder|OrderAddress wherePostcode($value)
 * @method static Builder|OrderAddress whereRegion($value)
 * @method static Builder|OrderAddress whereStateCode($value)
 * @method static Builder|OrderAddress whereStateName($value)
 * @method static Builder|OrderAddress whereUpdatedAt($value)
 * @method static Builder|OrderAddress whereWebsite($value)
 *
 * @mixin Eloquent
 */
class OrderAddress extends BaseModel
{
    use HasFactory;

    protected $table = 'orders_addresses';

    protected $fillable = [
        'company',
        'gender',
        'first_name',
        'last_name',
        'email',
        'address1',
        'address2',
        'postcode',
        'city',
        'state_code',
        'state_name',
        'country_code',
        'country_name',
        'phone',
        'fax',
        'website',
        'region',
    ];

    protected $appends = [
        'first_name',
        'last_name',
        'phone',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'phone_encrypted' => 'encrypted',
        ];
    }

    public function getFirstNameAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['first_name_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name_encrypted'] = Crypt::encryptString($value);
    }

    public function getLastNameAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['last_name_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setLastNameAttribute($value): void
    {
        $this->attributes['last_name_encrypted'] = Crypt::encryptString($value);
    }

    public function getPhoneAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['phone_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone_encrypted'] = Crypt::encryptString($value);
    }

    public function getEmailAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['email_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email_encrypted'] = Crypt::encryptString($value);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    protected function setFullNameAttribute(string $value): void
    {
        $this->first_name = explode(' ', $value)[0];
        $this->last_name = explode(' ', $value)[1];
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(OrderAddress::class)
            ->allowedFilters([AllowedFilter::scope('search', 'whereHasText')]);
    }

    public function scopeWhereHasText(mixed $query, string $text): mixed
    {
        return $query->where('company', $text)
            ->orWhere('company', 'like', '%' . $text . '%')
            ->orWhere('address1', $text)
            ->orWhere('address1', 'like', '%' . $text . '%')
            ->orWhere('address2', $text)
            ->orWhere('address2', 'like', '%' . $text . '%')
            ->orWhere('postcode', $text)
            ->orWhere('postcode', 'like', '%' . $text . '%')
            ->orWhere('city', $text)
            ->orWhere('city', 'like', '%' . $text . '%');
    }
}
