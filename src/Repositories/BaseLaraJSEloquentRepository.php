<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaraJS\Core\Services\QueryService;

abstract class BaseLaraJSEloquentRepository implements BaseLaraJSRepositoryInterface
{
    protected Model $model;

    protected int $limit;

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->setModel();
        $this->setLimit();
    }

    abstract public function getModel(): string;

    abstract public function getLimit(): int;

    /**
     * @throws BindingResolutionException
     */
    public function setModel()
    {
        $this->model = app()->make($this->getModel());
    }

    public function setLimit()
    {
        $this->limit = $this->getLimit();
    }

    public function index(Request $request, array $options = []): Builder|LengthAwarePaginator
    {
        $queryBuilder = $this->queryBuilder($request, $options);
        $isBuilder = $options['isBuilder'] ?? false;
        if ($isBuilder) {
            return $queryBuilder;
        }

        return $queryBuilder->paginate($request->get('limit', $this->limit));
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
        $queryService = new QueryService($this->model);

        return $queryService->filters([
            'select' => $options['select'] ?? [],
            'columnSearch' => [...$options['columnSearch'] ?? [], ...$request->get('column_search', [])],
            'withRelationship' => [...$options['withRelationship'] ?? [], ...$request->get('relationship', [])],
            'customQuery' => $options['customQuery'] ?? [],
            'columnDate' => $request->get('column_date') ?? ($options['columnDate'] ?? ''),
            'search' => $request->get('search'),
            'betweenDate' => $request->get('between_date'),
            'direction' => $request->get('direction'),
            'orderBy' => $request->get('orderBy'),
            'limit' => $request->get('limit'),
        ])->query();
    }
}
