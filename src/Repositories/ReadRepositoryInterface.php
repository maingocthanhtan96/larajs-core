<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * @template T
 */
interface ReadRepositoryInterface
{
    /**
     * @param  Request  $request
     * @return LengthAwarePaginator|CursorPaginator|Paginator|Collection<int, T>
     */
    public function findAll(Request $request): LengthAwarePaginator|CursorPaginator|Paginator|Collection;

    /**
     * @param  int  $id
     * @param  ?Request  $request
     * @return T
     */
    public function find(int $id, ?Request $request = null);

    /**
     * @param  int  $id
     * @param  ?Request  $request
     * @return T
     *
     * @throws ModelNotFoundException<T>
     */
    public function findOrFail(int $id, ?Request $request = null);

    /**
     * @return Builder<T>
     */
    public function query(): Builder;
}
