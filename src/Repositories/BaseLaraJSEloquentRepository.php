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

abstract class BaseLaraJSEloquentRepository implements BaseLaraJSRepositoryInterface
{
    use LaraJSQueryParser;

    protected Model $model;

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

    public function setLimit(): void
    {
        $this->limit = $this->getLimit();
    }

    public function setMaxLimit(): void
    {
        $this->maxLimit = $this->getMaxLimit();
    }

    /**
     * @throws Exception
     */
    public function index(Request $request, array $options = []): Builder|LengthAwarePaginator|Collection
    {
        $queryBuilder = $this->queryBuilder($request, $options);
        $isBuilder = $options['isBuilder'] ?? false;
        if ($isBuilder) {
            return $queryBuilder;
        }
        $queryBuilder = $this->applyQueryBuilder($queryBuilder, $request);

        if ($request->get('page') === '-1') {
            return $queryBuilder->take($this->maxLimit)->get();
        }

        return $queryBuilder->paginate(min($request->get('limit', $this->limit), $this->maxLimit));
    }

    public function store(array $data): Model
    {
        $model = new $this->model();
        $model->fill($data);
        $model->save();

        return $model;
    }

    public function show(int $id, array $relationship = []): Model
    {
        return $this->model->with($relationship)->findOrFail($id);
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->model->findOrFail($id);
        $model->fill($data);
        $model->save();

        return $model;
    }

    public function destroy(int $id): bool
    {
        $model = $this->show($id);

        return $model->delete();
    }

    public function all(array $relationship = []): Collection
    {
        return $this->model->with($relationship)->get();
    }

    public function queryBuilder(Request $request, array $options): Builder
    {
        return $this->model->query();
    }
}
