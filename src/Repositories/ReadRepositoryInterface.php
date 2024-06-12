<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * @template T
 */
interface ReadRepositoryInterface
{
    /**
     * @param  Request  $request
     * @param  array  $options
     * @return LengthAwarePaginator|CursorPaginator|Paginator|Collection
     */
    public function findAll(Request $request, array $options = []): LengthAwarePaginator|CursorPaginator|Paginator|Collection;

    /**
     * @param  int  $id
     * @param  Request  $request
     * @param  array  $options
     * @return T
     */
    public function find(int $id, Request $request, array $options = []);

    /**
     * @return Builder
     */
    public function query(): Builder;
}
