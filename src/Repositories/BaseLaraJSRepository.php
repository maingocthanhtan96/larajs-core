<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @template T
 *
 * @implements QueryRepositoryInterface<T>
 * @implements CommandRepositoryInterface<T>
 */
abstract class BaseLaraJSRepository implements QueryRepositoryInterface, CommandRepositoryInterface
{
    /** @var Model */
    protected Model $model;

    /** @var int */
    protected readonly int $limit;

    /** @var int */
    protected readonly int $maxLimit;

    private readonly CommandRepository $commandRepository;

    private readonly QueryRepository $queryRepository;

    abstract public function getModel(): string;

    abstract public function getLimit(): int;

    abstract public function getMaxLimit(): int;

    public function __construct()
    {
        $this->setModel();
        $this->setLimit();
        $this->setMaxLimit();
        $this->commandRepository = new CommandRepository($this->model);
        $this->queryRepository = new QueryRepository($this->model, $this->limit, $this->maxLimit);
    }

    private function setModel(): void
    {
        $this->model = app()->make($this->getModel());
    }

    private function setLimit(): void
    {
        $this->limit = $this->getLimit();
    }

    private function setMaxLimit(): void
    {
        $this->maxLimit = $this->getMaxLimit();
    }

    /**
     * @param  array  $data
     * @return T
     */
    public function create(array $data)
    {
        return $this->commandRepository->create($data);
    }

    /**
     * @param  int  $id
     * @param  array  $data
     * @return T
     */
    public function update(int $id, array $data)
    {
        return $this->commandRepository->update($id, $data);
    }

    /**
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->commandRepository->delete($id);
    }

    /**
     * @param  Model  $model
     * @param  array  $data
     * @return T
     */
    public function save(Model $model, array $data)
    {
        return $this->commandRepository->save($model, $data);
    }

    /**
     * @param  Request  $request
     * @param  array  $options
     * @return LengthAwarePaginator|T[]
     */
    public function findAll(Request $request, array $options = []): LengthAwarePaginator|Collection
    {
        return $this->queryRepository->findAll($request, $options);
    }

    /**
     * @param  int  $id
     * @param  Request  $request
     * @param  array  $options
     * @return T
     */
    public function find(int $id, Request $request, array $options = [])
    {
        return $this->queryRepository->find($id, $request, $options);
    }

    /**
     * @return Builder
     */
    public function queryBuilder(): Builder
    {
        return $this->queryRepository->queryBuilder();
    }
}
