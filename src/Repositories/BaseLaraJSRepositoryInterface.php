<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @template TModel
 */
interface BaseLaraJSRepositoryInterface
{
    /**
     * @param Request $request
     * @param array $options
     * @return LengthAwarePaginator|TModel[]
     */
    public function list(Request $request, array $options): LengthAwarePaginator|Collection;

    /**
     * @param array $data
     * @return TModel
     */
    public function create(array $data): Model;

    /**
     * @param int $id
     * @param Request $request
     * @param array $options
     * @return ?TModel
     */
    public function find(int $id, Request $request, array $options): ?Model;

    /**
     * @param int $id
     * @param array $data
     * @return TModel
     */
    public function update(int $id, array $data): Model;

    /**
     * @param int $id
     * @return bool
     */
    public function destroy(int $id): bool;

    /**
     * @return Builder
     */
    public function queryBuilder(): Builder;

    /**
     * @param Model $model
     * @param array $data
     * @return TModel
     */
    public function save(Model $model, array $data): Model;
}
