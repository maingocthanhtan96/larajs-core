<?php

namespace LaraJS\Core;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;
use LaraJS\Core\Commands\SetupCommand;

class GeneratorProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected bool $defer = false;

    public function boot()
    {
        $this->app->singleton('larajs.setup', function () {
            return new SetupCommand;
        });

        $this->commands(
            'larajs.setup'
        );
    }

    public function register()
    {
        $this->_whereLike();
        $this->_paginate();
        $this->_orderByRelationship();
    }

    private function _whereLike()
    {
        // whereLike
        Builder::macro('whereLike', function ($attributes, string $searchTerm) {
            $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                foreach (Arr::wrap($attributes) as $attribute) {
                    $query->when(
                        Str::contains($attribute, '.'),
                        function (Builder $query) use ($attribute, $searchTerm) {
                            [$relationName, $relationAttribute] = explode('.', $attribute);

                            $query->orWhereHas($relationName, function (Builder $query) use (
                                $relationAttribute,
                                $searchTerm,
                            ) {
                                $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                            });
                        },
                        function (Builder $query) use ($attribute, $searchTerm) {
                            $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                        },
                    );
                }
            });

            return $this;
        });
    }

    private function _paginate()
    {
        // Enable pagination
        if (!Collection::hasMacro('paginate')) {
            Collection::macro('paginate', function ($perPage = 15, $page = null, $options = []) {
                $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                return (new LengthAwarePaginator(
                    $this->forPage($page, $perPage)
                        ->values()
                        ->all(),
                    $this->count(),
                    $perPage,
                    $page,
                    $options,
                ))->withPath(Request::url());
            });
        }
    }

    private function _orderByRelationship()
    {
        Builder::macro('orderByRelationship', function ($searchColumn, string $direction = 'asc') {
            if (Str::contains($searchColumn, '.')) {
                [$relation, $column] = explode('.', $searchColumn);
                $relation = $this->getRelation($relation);
                if ($relation instanceof BelongsToMany) {
                    $tableThrough = $relation->getTable();
                    $relationForeignKey = $relation->getForeignPivotKeyName();
                    $relationRelatedKey = $relation->getRelatedPivotKeyName();
                    $queryTable = $this->getModel()->getTable();
                    $queryTableRelated = $relation->getModel()->getTable();

                    return $this->select("$queryTable.*")
                        ->join($tableThrough, "$tableThrough.$relationForeignKey", "$queryTable.id")
                        ->join($queryTableRelated, "$queryTableRelated.id", "$tableThrough.$relationRelatedKey")
                        ->orderBy("$queryTableRelated.$column", $direction);
                } else {
                    $relationTable = $relation->getModel()->getTable();
                    $relationForeignKey = $relation->getForeignKeyName();
                    $queryTable = $this->getModel()->getTable();

                    return $this->select("$queryTable.*")
                        ->join($relationTable, "$queryTable.$relationForeignKey", "$relationTable.id")
                        ->orderBy("$relationTable.$column", $direction);
                }
            }

            return $this->orderBy($searchColumn, $direction);
        });
    }
}