<?php

namespace LaraJS\Core\Services;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class QueryService
{
    public const LIMIT = 25;

    /**
     * Select column owner
     *
     * @var array
     */
    public array $select = [];

    /**
     * Column to search using whereLike
     *
     * @var array
     */
    public array $columnSearch = [];

    /**
     * Relationship with other tables
     *
     * @var array
     */
    public array $withRelationship = [];

    /**
     * Paragraph search in column
     *
     * @var ?string
     */
    public ?string $search = '';

    /**
     * Start date - End date
     *
     * @var array
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
     *
     * @var string
     */
    public string $orderBy = '';

    /**
     * Always order this column
     *
     * @var string
     */
    public string $columnDate = 'updated_at';

    /**
     * Limit records
     *
     * @var int
     */
    public int $limit = self::LIMIT;

    /**
     * Add dynamic query
     *
     * @var callable
     */
    public $customQuery = null;

    /**
     * QueryService constructor.
     *
     * @param  Model  $model
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
     *
     * @return Builder
     */
    public function query(): Builder
    {
        $query = $this->model::query();
        $query->when($this->select, fn(Builder $q) => $q->select($this->select));
        $query->when($this->search, fn(Builder $q) => $q->whereLike($this->columnSearch, $this->search));
        $query->with(Arr::wrap($this->withRelationship));
        $query->when(is_callable($this->customQuery), $this->customQuery);
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
     * @param  array  $filters
     *
     * @property string $select
     * @property array $columnSearch
     * @property string $search
     * @property array $betweenDate
     * @property string $direction
     * @property string $orderBy
     * @property int $limit
     * @property callable $customQuery
     */
    public function filters(array $filters)
    {
        foreach ($filters as $field => $filter) {
            $filter && ($this->{$field} = $filter);
        }
    }
}
