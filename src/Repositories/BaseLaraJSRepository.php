<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @template T
 *
 * @implements ReadRepositoryInterface<T>
 * @implements WriteRepositoryInterface<T>
 */
abstract class BaseLaraJSRepository implements ReadRepositoryInterface, WriteRepositoryInterface
{
    /** @var Model */
    protected Model $model;

    /** @var int */
    protected readonly int $limit;

    /** @var int */
    protected readonly int $maxLimit;

    private readonly WriteRepository $writeRepository;

    private readonly ReadRepository $readRepository;

    abstract public function getModel(): string;

    abstract public function getLimit(): int;

    abstract public function getMaxLimit(): int;

    public function __construct()
    {
        $this->setModel();
        $this->setLimit();
        $this->setMaxLimit();
        $this->writeRepository = new WriteRepository($this->model);
        $this->readRepository = new ReadRepository($this->model, $this->limit, $this->maxLimit);
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
        return $this->writeRepository->create($data);
    }

    /**
     * @param  int  $id
     * @param  array  $data
     * @return T
     */
    public function update(int $id, array $data)
    {
        return $this->writeRepository->update($id, $data);
    }

    /**
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->writeRepository->delete($id);
    }

    /**
     * @param  Model  $model
     * @param  array  $data
     * @return T
     */
    public function save(Model $model, array $data)
    {
        return $this->writeRepository->save($model, $data);
    }

    /**
     * @param  Request  $request
     * @param  array  $options
     * @return LengthAwarePaginator|CursorPaginator|Paginator|T[]
     */
    public function findAll(Request $request, array $options = []): LengthAwarePaginator|CursorPaginator|Paginator|Collection
    {
        return $this->readRepository->findAll($request, $options);
    }

    /**
     * @param  int  $id
     * @param  Request  $request
     * @param  array  $options
     * @return T
     */
    public function find(int $id, Request $request, array $options = [])
    {
        return $this->readRepository->find($id, $request, $options);
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->readRepository->query();
    }
}
