<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
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
     * @return LengthAwarePaginator|CursorPaginator|Paginator|T[]
     */
    public function findAll(Request $request, array $options = []): LengthAwarePaginator|CursorPaginator|Paginator|Collection
    {
        $queryBuilder = $this->applyQueryBuilder($this->queryBuilder(), $request, $options);
        if ($request->get('page') === '-1') {
            $limit = min($this->maxLimit, $request->input('pagination.limit'));

            return $queryBuilder->take($limit)->get();
        }
        $limit = min($request->input('pagination.limit', $this->limit), $this->maxLimit);

        return match ($request->input('pagination.type')) {
            'simple' => $queryBuilder->simplePaginate($limit, pageName: 'pagination[page]'),
            'cursor' => $queryBuilder->cursorPaginate($limit, cursorName: 'pagination[cursor]'),
            default => $queryBuilder->paginate($limit, pageName: 'pagination[page]'),
        };
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
