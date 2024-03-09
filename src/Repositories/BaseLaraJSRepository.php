<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Database\Eloquent\Model;

/**
 * @template T
 *
 * @extends QueryRepository<T>
 *
 * @implements CommandRepositoryInterface<T>
 */
abstract class BaseLaraJSRepository extends QueryRepository implements CommandRepositoryInterface
{
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
}
