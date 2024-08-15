<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use LaraJS\QueryParser\LaraJSQueryParser;

/**
 * @template T
 *
 * @implements ReadRepositoryInterface<T>
 */
class ReadRepository implements ReadRepositoryInterface
{
    use LaraJSQueryParser;

    /**
     * @param  Model  $model
     * @param  int  $limit
     * @param  int  $maxLimit
     */
    public function __construct(protected readonly Model $model, protected readonly int $limit, protected readonly int $maxLimit) {}

    /**
     * @param  Request  $request
     * @return LengthAwarePaginator|CursorPaginator|Paginator|Collection<int, T>
     */
    public function findAll(Request $request): LengthAwarePaginator|CursorPaginator|Paginator|Collection
    {
        $queryBuilder = $this->applyQueryBuilder($this->query(), $request);
        if ($request->input('pagination.page') === '-1') {
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
     * @param  ?Request  $request
     * @return T
     */
    public function find(int $id, ?Request $request = null)
    {
        return $this->applyQueryBuilder($this->query(), $request)->find($id);
    }

    /**
     * @param  int  $id
     * @param  ?Request  $request
     * @return T
     *
     * @throws ModelNotFoundException<T>
     */
    public function findOrFail(int $id, ?Request $request = null)
    {
        return $this->applyQueryBuilder($this->query(), $request)->findOrFail($id);
    }

    /**
     * @return Builder<T>
     */
    public function query(): Builder
    {
        return $this->model->query();
    }
}
