<?php

namespace LaraJS\Core\Repositories;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaraJS\QueryParser\LaraJSQueryParser;

/**
 * @template TModel
 * @template-implements BaseLaraJSRepositoryInterface<TModel>
 */
abstract class  BaseLaraJSEloquentRepository implements BaseLaraJSRepositoryInterface
{
    use LaraJSQueryParser;

    public Model $model;

    protected int $limit;

    protected int $maxLimit;

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
     * @param Request $request
     * @param array $options
     * @return LengthAwarePaginator|TModel[]
     */
    public function list(Request $request, array $options = []): LengthAwarePaginator|Collection
    {
        $queryBuilder = $this->applyQueryBuilder($this->queryBuilder(), $request, $options);

        if ($request->get('page') === '-1') {
            return $queryBuilder->take($this->maxLimit)->get();
        }

        return $queryBuilder->paginate(min($request->get('limit', $this->limit), $this->maxLimit));
    }

    /**
     * @param array $data
     * @return TModel
     */
    public function create(array $data): Model
    {
        return $this->save(new $this->model(), $data);
    }

    /**
     * @param int $id
     * @param Request $request
     * @param array $options
     * @return TModel
     */
    public function find(int $id, Request $request, array $options = []): Model
    {
        return $this->applyQueryBuilder($this->queryBuilder(), $request, $options)->findOrFail($id);
    }

    /**
     * @param int $id
     * @param array $data
     * @return TModel
     */
    public function update(int $id, array $data): Model
    {
        return $this->save($this->model->findOrFail($id), $data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }


    public function save(Model $model, array $data): Model
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
