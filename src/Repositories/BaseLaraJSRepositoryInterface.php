<?php

namespace LaraJS\Core\Repositories;

/**
 * @template T
 *
 * @extends QueryRepositoryInterface<T>
 * @extends CommandRepositoryInterface<T>
 */
interface BaseLaraJSRepositoryInterface extends QueryRepositoryInterface, CommandRepositoryInterface
{
}
