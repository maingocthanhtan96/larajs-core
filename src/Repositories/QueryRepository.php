<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaraJS\QueryParser\LaraJSQueryParser;

/**
 * @template T
 *
 * @implements QueryRepositoryInterface<T>
 */
class QueryRepository implements QueryRepositoryInterface
{
    use LaraJSQueryParser;

    /**
     * @param  Model  $model
     * @param  int  $limit
     * @param  int  $maxLimit
     */
    public function __construct(protected readonly Model $model, protected readonly int $limit, protected readonly int $maxLimit)
    {
    }

    /**
     * @param  Request  $request
     * @param  array  $options
     * @return LengthAwarePaginator|Collection
     */
    public function findAll(Request $request, array $options = []): LengthAwarePaginator|Collection
    {
        $queryBuilder = $this->applyQueryBuilder($this->queryBuilder(), $request, $options);
        if ($request->get('page') === '-1') {
            if ($this->maxLimit > 0) {
                $queryBuilder->take($this->maxLimit);
            }

            return $queryBuilder->get();
        }
        $overrideLimit = 100;
        $limit = min($request->get('limit', $this->limit), $this->maxLimit ?: $overrideLimit);

        return $queryBuilder->paginate($limit);
    }

    /**
     * @param  int  $id
     * @param  Request  $request
     * @param  array  $options
     * @return T
     */
    public function find(int $id, Request $request, array $options = [])
    {
        return $this->applyQueryBuilder($this->queryBuilder(), $request, $options)->findOrFail($id);
    }

    /**
     * @return Builder
     */
    public function queryBuilder(): Builder
    {
        return $this->model->query();
    }
}
