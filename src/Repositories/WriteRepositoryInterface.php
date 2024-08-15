<?php

namespace LaraJS\Core\Repositories;

/**
 * @template T
 */
interface WriteRepositoryInterface
{
    /**
     * @param  array  $data
     * @return T
     */
    public function create(array $data);

    /**
     * @param  int  $id
     * @param  array  $data
     * @return T
     */
    public function update(int $id, array $data);

    /**
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;
}
