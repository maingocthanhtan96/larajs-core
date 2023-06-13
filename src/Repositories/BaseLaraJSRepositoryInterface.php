<?php

namespace LaraJS\Core\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BaseLaraJSRepositoryInterface
{
    public function index(Request $request, array $options): Builder|LengthAwarePaginator;

    public function store(array $data): Model;

    public function show(int $id, array $relationship): ?Model;

    public function update(int $id, array $data): Model;

    public function destroy(int $id): bool;

    public function all(array $relationship): Collection;

    public function queryBuilder(Request $request, array $options): Builder;

    public function handleFilters(Request $request, array $options): array;
}
