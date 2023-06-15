<?php

namespace LaraJS\Core\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class QueryService
{
    /**
     * Select column owner
     */
    public array $select = [];

    /**
     * Column to search using whereLike
     */
    public array $columnSearch = [];

    /**
     * Relationship with other tables
     */
    public array $withRelationship = [];

    /**
     * Aggregate with other tables
     */
    public array $withAggregate = [];

    /**
     * Paragraph search in column
     *
     * @var ?string
     */
    public ?string $search = '';

    /**
     * Start date - End date
     */
    public array $betweenDate = [];

    /**
     * ascending, descending
     *
     * @var ?string
     */
    public ?string $direction = '';

    /**
     * Column to order
     */
    public string $orderBy = '';

    /**
     * Always order this column
     */
    public string $columnDate = 'updated_at';

    /**
     * QueryService constructor.
     *
     *
     * @author tanmnt
     */
    public function __construct(private readonly Model $model)
    {
    }

    /**
     * Query table
     *
     * @author tanmnt
     */
    public function query(): Builder
    {
        $query = $this->model::query();
        $query->when($this->select, fn(Builder $q) => $q->select($this->select));
        $query->when($this->search, fn(Builder $q) => $q->whereLike($this->columnSearch, $this->search));
        $query->when($this->withRelationship, fn(Builder $q) => $q->with(Arr::wrap($this->withRelationship)));
        $query->when($this->withAggregate, function (Builder $q) {
            foreach (Arr::wrap($this->withAggregate) as $withSum) {
                if (Str::contains($withSum, '|')) {
                    [$relationColumn, $function] = explode('|', $withSum);
                    $relationColumn = explode('.', $relationColumn);
                    $function = strtolower($function);
                    $q->withAggregate(
                        $relationColumn[0],
                        in_array($function, ['count', 'exists']) ? '*' : $relationColumn[1],
                        $function,
                    );
                }
            }
        });
        $query->when(isset($this->betweenDate[0]) && isset($this->betweenDate[1]), function (Builder $q) {
            $startDate = Carbon::parse($this->betweenDate[0])->startOfDay();
            $endDate = Carbon::parse($this->betweenDate[1])->endOfDay();
            $q->whereBetween($this->columnDate, [$startDate, $endDate]);
        });
        $query->when(
            $this->orderBy && $this->direction,
            fn(Builder $q) => $q->orderByRelationship($this->orderBy, convert_direction($this->direction)),
        );

        return $query;
    }

    /**
     * @property string $search
     * @property string $select
     * @property string $columnDate
     * @property string $direction
     * @property string $orderBy
     * @property array $columnSearch
     * @property array $betweenDate
     * @property array $withRelationship
     * @property array $withAggregate
     */
    public function filters(array $filters): static
    {
        foreach ($filters as $field => $filter) {
            $filter && ($this->{$field} = $filter);
        }

        return $this;
    }
}
