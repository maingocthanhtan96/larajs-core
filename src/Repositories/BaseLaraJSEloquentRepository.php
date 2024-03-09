<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaraJS\QueryParser\LaraJSQueryParser;

/**
 * @template T
 *
 * @template-implements BaseLaraJSRepositoryInterface<T>
 */
abstract class BaseLaraJSEloquentRepository implements BaseLaraJSRepositoryInterface
{
    use LaraJSQueryParser;

    /** @var Model */
    public Model $model;

    /** @var int */
    protected int $limit;

    /** @var int */
    protected int $maxLimit;

    /** @var int */
    private int $overrideLimit = 100;

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->setModel();
        $this->setLimit();
        $this->setMaxLimit();
    }

    abstract public function getModel(): string;

    abstract public function getLimit(): int;

    abstract public function getMaxLimit(): int;

    /**
     * @throws BindingResolutionException
     */
    public function setModel(): void
    {
        $this->model = app()->make($this->getModel());
    }

    /**
     * @return void
     */
    public function setLimit(): void
    {
        $this->limit = $this->getLimit();
    }

    /**
     * @return void
     */
    public function setMaxLimit(): void
    {
        $this->maxLimit = $this->getMaxLimit();
    }

    /**
     * @param  Request  $request
     * @param  array  $options
     * @return LengthAwarePaginator|T[]
     */
    public function list(Request $request, array $options = []): LengthAwarePaginator|Collection
    {
        $queryBuilder = $this->applyQueryBuilder($this->queryBuilder(), $request, $options);
        if ($request->get('page') === '-1') {
            if ($this->maxLimit > 0) {
                $queryBuilder->take($this->maxLimit);
            }

            return $queryBuilder->get();
        }
        $limit = min($request->get('limit', $this->limit), $this->maxLimit ?: $this->overrideLimit);

        return $queryBuilder->paginate($limit);
    }

    /**
     * @param  array  $data
     * @return T
     */
    public function create(array $data)
    {
        return $this->save(new $this->model(), $data);
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
     * @param  int  $id
     * @param  array  $data
     * @return T
     */
    public function update(int $id, array $data)
    {
        return $this->save($this->model->findOrFail($id), $data);
    }

    /**
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    /**
     * @param  Model  $model
     * @param  array  $data
     * @return T
     */
    public function save(Model $model, array $data)
    {
        $model->fill($data)->save();

        return $model;
    }

    /**
     * @return Builder
     */
    public function queryBuilder(): Builder
    {
        return $this->model->query();
    }
}
