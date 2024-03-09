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
 * @implements QueryRepositoryInterface<T>
 */
abstract class QueryRepository implements QueryRepositoryInterface
{
    use LaraJSQueryParser;

    /** @var Model */
    protected Model $model;

    /** @var int */
    protected int $limit;

    /** @var int */
    protected int $maxLimit;

    /** @var int */
    private int $overrideLimit = 100;

    abstract public function getLimit(): int;

    abstract public function getMaxLimit(): int;

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->setModel();
        $this->setLimit();
        $this->setMaxLimit();
    }

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
