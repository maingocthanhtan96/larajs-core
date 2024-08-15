<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * @param  Request  $request
     * @return LengthAwarePaginator|CursorPaginator|Paginator|Collection<int, T>
     */
    public function findAll(Request $request): LengthAwarePaginator|CursorPaginator|Paginator|Collection
    {
        return $this->readRepository->findAll($request);
    }

    /**
     * @param  int  $id
     * @param  ?Request  $request
     * @return T
     */
    public function find(int $id, ?Request $request = null)
    {
        return $this->readRepository->find($id, $request);
    }

    /**
     * @param  int  $id
     * @param  ?Request  $request
     * @return T
     *
     * @throws ModelNotFoundException<T>
     */
    public function findOrFail(int $id, ?Request $request = null)
    {
        return $this->readRepository->findOrFail($id, $request);
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->readRepository->query();
    }
}
