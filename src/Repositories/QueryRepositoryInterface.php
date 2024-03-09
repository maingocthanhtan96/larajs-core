<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * @template T
 */
interface QueryRepositoryInterface
{
    /**
     * @param  Request  $request
     * @param  array  $options
     * @return LengthAwarePaginator|T[]
     */
    public function list(Request $request, array $options = []): LengthAwarePaginator|Collection;

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
    public function queryBuilder(): Builder;
}
