<?php

namespace App\Modules\Reports\src\Models;

use App\Exceptions\InvalidSelectException;
use App\Traits\HasTagsTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReportBase extends Model
{
    use HasTagsTrait;

    public $table = 'report';

    public string $report_name = 'Report';

    public string $view = 'report-default';

    public array $defaultSelect = [];

    public ?string $defaultSort = null;

    public array $fields = [];

    public array $allFields = [];

    public mixed $baseQuery;

    public array $allowedFilters = [];

    public array $allowedIncludes = [];

    protected array $fieldAliases = [];

    protected $casts = [];

    public function getRecords(): Collection|array
    {
        $records = $this->getFinalQuery()->get();

        if (empty($this->fields) && $records->isNotEmpty()) {
            $this->fields = $records->first()->toArray();
        }

        return $records;
    }

    public function getFinalQuery(): QueryBuilder
    {
        $limit = request('per_page', 50);
        $page = request('page', 1);

        $offset = ($page - 1) * $limit;

        return $this->queryBuilder()
            ->offset($offset)
            ->limit($limit);
    }

    public function queryBuilder(): QueryBuilder
    {
        $this->fieldAliases = [];

        foreach ($this->fields as $alias => $field) {
            $this->fieldAliases[] = $alias;
        }

        $queryBuilder = QueryBuilder::for($this->baseQuery);

        $queryBuilder = $this->addSelectFields($queryBuilder);

        if ($this->defaultSort) {
            $queryBuilder = $queryBuilder->defaultSort($this->defaultSort);
        }

        $queryBuilder = $queryBuilder
            ->allowedFilters($this->getAllowedFilters())
            ->allowedSorts($this->fieldAliases)
            ->allowedIncludes($this->allowedIncludes);

        return $queryBuilder;
    }

    public function getMetaData(): array
    {
        return [
            'report_name' => $this->report_name ?? $this->table,
            'pagination' => [
                'per_page' => request('per_page', 50),
                'page' => request('page', 1),
            ],
            'field_links' => collect(array_keys($this->fields))
                ->map(fn ($field) => [
                    'name' => $field,
                    'display_name' => Str::headline($field),
                    'type' => $this->getFieldType($field),
                    'operators' => $this->getFieldTypeOperators($field),
                ]),
        ];
    }

    public function addFilter(AllowedFilter $filter): self
    {
        $this->allowedFilters[] = $filter;

        return $this;
    }

    public function addAllowedInclude($include): self
    {
        $this->allowedIncludes[] = $include;

        return $this;
    }

    /**
     * @throws Exception
     */
    private function getAllowedFilters(): array
    {
        $filters = collect($this->allowedFilters);

        $filters = $filters->merge($this->addExactFilters());
        $filters = $filters->merge($this->addContainsFilters());
        $filters = $filters->merge($this->addInFilters());
        $filters = $filters->merge($this->addNotInFilters());
        $filters = $filters->merge($this->addBetweenStringFilters());
        $filters = $filters->merge($this->addBetweenFloatFilters());
        $filters = $filters->merge($this->addBetweenDatesFilters());
        $filters = $filters->merge($this->addGreaterThan());
        $filters = $filters->merge($this->addLowerThan());
        $filters = $filters->merge($this->addNullFilters());
        $filters = $filters->merge($this->addNotEqualFilters());

        return $filters->toArray();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidSelectException
     */
    private function addSelectFields(QueryBuilder $queryBuilder): QueryBuilder
    {
        $requestedSelect = collect(explode(',', request()->get('select', '')))->filter();

        if ($requestedSelect->isEmpty()) {
            $requestedSelect = collect($this->defaultSelect);
        }

        if ($requestedSelect->isEmpty()) {
            $requestedSelect = collect(array_keys($this->fields));
        }

        $requestedSelect
            ->each(function ($selectFieldName) use ($queryBuilder) {
                $field = data_get($this->allFields, $selectFieldName);

                if ($field && $field['displayable'] === false) {
                    return;
                }

                $fieldValue = data_get($this->fields, $selectFieldName);

                if ($fieldValue === null) {
                    throw new InvalidSelectException('Requested select field(s) `'.$selectFieldName.'` are not allowed.
                    Allowed select(s) are '.collect(array_keys($this->fields))->implode(','));
                }

                if ($fieldValue instanceof Expression) {
                    $queryBuilder->addSelect(DB::raw('('.$fieldValue->getValue($queryBuilder->getGrammar()).') as '.$selectFieldName));

                    return;
                }

                $queryBuilder->addSelect($fieldValue.' as '.$selectFieldName);
            });

        return $queryBuilder;
    }

    private function addExactFilters(): array
    {
        $allowedFilters = [];

        // add exact filters
        collect($this->fields)
            ->each(function ($full_field_name, $alias) use (&$allowedFilters) {
                $allowedFilters[] = AllowedFilter::callback($alias, function ($query, $value) use ($full_field_name) {
                    return $query->where($full_field_name, '=', $value);
                });
            });

        return $allowedFilters;
    }

    private function addContainsFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($value, $key) {
                $type = data_get($this->casts, $key);

                return in_array($type, ['string', null]);
            })
            ->each(function ($record, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_contains';

                if ($record instanceof Expression) {
                    $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($record) {
                        $query->where(new Expression('('.$record->getValue(DB::connection()->getQueryGrammar()).')'), 'LIKE', "%{$value}%");
                    });
                } else {
                    $allowedFilters[] = AllowedFilter::partial($filterName, $record);
                }
            });

        return $allowedFilters;
    }

    private function addBetweenFloatFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($fieldQuery, $fieldName) {
                $type = data_get($this->casts, $fieldName, 'string');

                return $type === 'float';
            })
            ->each(function ($fieldType, $fieldAlias) use (&$allowedFilters) {
                $filterName = $fieldAlias.'_between';
                $fieldQuery = $this->fields[$fieldAlias];

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($fieldQuery) {
                    // we add this to make sure query returns no records if array of two values is not specified
                    if ((! is_array($value)) or (count($value) != 2)) {
                        $query->whereRaw('1=2');

                        return;
                    }

                    if ($fieldQuery instanceof Expression) {
                        $query->whereBetween(DB::raw('('.$fieldQuery.')'), [floatval($value[0]), floatval($value[1])]);

                        return;
                    }

                    $query->whereBetween($fieldQuery, [floatval($value[0]), floatval($value[1])]);
                });
            });

        return $allowedFilters;
    }

    private function addNotEqualFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->each(function ($fullFieldName, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_not equal';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($fullFieldName) {
                    return $query->where($fullFieldName, '!=', $value);
                });
            });

        return $allowedFilters;
    }

    /**
     * @throws Exception
     */
    private function addBetweenDatesFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($fieldQuery, $fieldName) {
                $type = data_get($this->casts, $fieldName, 'string');

                return in_array($type, ['datetime', 'date']);
            })
            ->each(function ($fieldType, $fieldAlias) use (&$allowedFilters) {
                $filterName = $fieldAlias.'_between';
                $fieldQuery = data_get($this->fields, $fieldAlias);

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($filterName, $fieldQuery) {
                    // we add this to make sure query returns no records if array of two values is not specified
                    if ((! is_array($value)) or (count($value) != 2)) {
                        throw new Exception($filterName.': Invalid filter value, expected array of two values');
                    }

                    if ($fieldQuery instanceof Expression) {
                        $query->whereBetween(
                            DB::raw('('.$fieldQuery.')'),
                            [Carbon::parse($value[0]), Carbon::parse($value[1])]
                        );

                        return;
                    }

                    $query->whereBetween($fieldQuery, [Carbon::parse($value[0]), Carbon::parse($value[1])]);
                });
            });

        return $allowedFilters;
    }

    /**
     * @throws Exception
     */
    private function addGreaterThan(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($fieldQuery, $fieldName) {
                $type = data_get($this->casts, $fieldName, 'string');

                return in_array($type, ['string', 'datetime', 'float']);
            })
            ->each(function ($record, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_greater_than';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($alias) {
                    $query->where($this->fields[$alias], '>', $value);
                });
            });

        return $allowedFilters;
    }

    /**
     * @throws Exception
     */
    private function addGreaterThanFloat(): array
    {
        $allowedFilters = [];

        collect($this->casts)
            ->filter(function ($type) {
                return $type === 'float';
            })
            ->each(function ($record, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_greater_than';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($alias) {
                    $query->where($this->fields[$alias], '>', floatval($value));
                });
            });

        return $allowedFilters;
    }

    private function addLowerThan(): array
    {
        $allowedFilters = [];

        collect($this->casts)
            ->filter(function ($type) {
                return in_array($type, ['string', 'datetime', 'float']);
            })
            ->each(function ($type, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_lower_than';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($type, $alias) {
                    if ($type === 'float') {
                        $query->where($this->fields[$alias], '<', floatval($value));

                        return;
                    }

                    $query->where($this->fields[$alias], '<', $value);
                });
            });

        return $allowedFilters;
    }

    private function addNullFilters(): array
    {
        $allowedFilters = [];

        $allowedFilters[] = AllowedFilter::callback('null', function ($query, $value) {
            $query->whereNull($this->fields[$value]);
        });

        return $allowedFilters;
    }

    public function simplePaginatedCollection(): Paginator
    {
        return $this->queryBuilder()->simplePaginate(request()->get('per_page', 10));
    }

    private function addBetweenStringFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($fieldQuery, $fieldName) {
                $type = data_get($this->casts, $fieldName, 'string');

                return $type === 'string';
            })
            ->each(function ($fieldType, $fieldAlias) use (&$allowedFilters) {
                $filterName = $fieldAlias.'_between';
                $fieldQuery = $this->fields[$fieldAlias];

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($fieldQuery) {
                    // we add this to make sure query returns no records if array of two values is not specified
                    if ((! is_array($value)) or (count($value) != 2)) {
                        $query->whereRaw('1=2');

                        return;
                    }

                    if ($fieldQuery instanceof Expression) {
                        $query->whereBetween(DB::raw('('.$fieldQuery.')'), [floatval($value[0]), floatval($value[1])]);

                        return;
                    }

                    $query->whereBetween($fieldQuery, [$value[0], $value[1]]);
                });
            });

        return $allowedFilters;
    }

    private function addNotInFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->each(function ($type, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_not_in';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($alias) {
                    $query->whereNotIn($this->fields[$alias], explode(',', $value));
                });
            });

        return $allowedFilters;
    }

    private function addInFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->each(function ($type, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_in';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($alias) {
                    $query->whereIn($this->fields[$alias], explode(',', $value));
                });
            });

        return $allowedFilters;
    }

    protected function getFieldType($field): string
    {
        return match ($this->casts[$field] ?? null) {
            'float', 'integer' => 'numeric',
            default => $this->casts[$field] ?? 'string',
        };
    }

    protected function getFieldTypeOperators($field): array
    {
        return match ($this->getFieldType($field)) {
            'string' => ['equals', 'not equal', 'btwn', 'contains', 'greater than', 'lower than'],
            'numeric' => ['equals', 'not equal', 'btwn', 'greater than', 'lower than'],
            'date' => ['btwn'],
            'datetime' => ['btwn'],
            default => ['contains', 'equals', 'not equal'],
        };
    }
}
