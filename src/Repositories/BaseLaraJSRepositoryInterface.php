<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @template T
 */
interface BaseLaraJSRepositoryInterface
{
    /**
     * @param  Request  $request
     * @param  array  $options
     * @return LengthAwarePaginator|T[]
     */
    public function list(Request $request, array $options = []): LengthAwarePaginator|Collection;

    /**
     * @param  array  $data
     * @return T
     */
    public function create(array $data);

    /**
     * @param  int  $id
     * @param  Request  $request
     * @param  array  $options
     * @return T
     */
    public function find(int $id, Request $request, array $options = []);

    /**
     * @param  int  $id
     * @param  array  $data
     * @return T
     */
    public function update(int $id, array $data);

    /**
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * @return Builder
     */
    public function queryBuilder(): Builder;

    /**
     * @param  Model  $model
     * @param  array  $data
     * @return T
     */
    public function save(Model $model, array $data);
}
